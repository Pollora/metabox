<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes;

use InvalidArgumentException;
use LogicException;
use Pollora\Metabox\Attributes\Block\View;
use Pollora\Metabox\Attributes\Contracts\BlockSetting;
use Pollora\Metabox\Attributes\Contracts\FieldGroupSetting;
use Pollora\Metabox\Attributes\Contracts\Integration;
use Pollora\Metabox\Attributes\Contracts\SettingsPageSetting;
use Pollora\Metabox\Block as BlockBuilder;
use Pollora\Metabox\FieldGroup as FieldGroupBuilder;
use Pollora\Metabox\Location;
use Pollora\Metabox\SettingsPage as SettingsPageBuilder;
use ReflectionClass;

/**
 * Registers the classes declared with #[FieldGroup], #[Block] or #[SettingsPage].
 *
 * Each class is registered once, however many times it is passed, and built when
 * Meta Box asks for the declarations: its fields() method can rely on what plugins
 * and the theme set up before.
 */
final class Registrar
{
    /**
     * The registered declarations, by class.
     *
     * @var array<class-string, FieldGroup|Block|SettingsPage>
     */
    private static array $declarations = [];

    /**
     * The settings built for Meta Box, by class.
     *
     * @var array<class-string, array<string, mixed>>
     */
    private static array $built = [];

    private static ?Integration $integration = null;

    private static bool $hooked = false;

    /**
     * Connect the declared classes to a framework, such as Pollora with pollora/metabox-bridge.
     */
    public static function integrate(?Integration $integration): void
    {
        self::$integration = $integration;
    }

    /**
     * Register declared classes. A class already registered is skipped.
     *
     * @param  class-string  ...$classes
     */
    public static function register(string ...$classes): void
    {
        foreach ($classes as $class) {
            if (isset(self::$declarations[$class])) {
                continue;
            }

            $declaration = self::declaration($class);

            if (! $declaration instanceof SettingsPage && self::tooLate()) {
                throw new LogicException("The class '{$class}' is registered too late: register it before the init action, priority 20.");
            }

            self::checkId($class, $declaration);
            self::$declarations[$class] = $declaration;
        }

        self::hook();
    }

    /**
     * The registered declarations, by class.
     *
     * @return array<class-string, FieldGroup|Block|SettingsPage>
     */
    public static function registered(): array
    {
        return self::$declarations;
    }

    /**
     * Forget the registered classes and the integration.
     *
     * @internal
     */
    public static function reset(): void
    {
        self::$declarations = [];
        self::$built = [];
        self::$integration = null;
        self::$hooked = false;
    }

    /**
     * Add the declared field groups and blocks to the ones given to Meta Box.
     *
     * @internal Callback of the rwmb_meta_boxes filter.
     */
    public static function metaBoxes(mixed $metaBoxes): mixed
    {
        return self::add($metaBoxes, fn ($declaration): bool => ! $declaration instanceof SettingsPage);
    }

    /**
     * Add the declared settings pages to the ones given to MB Settings Page.
     *
     * @internal Callback of the mb_settings_pages filter.
     */
    public static function settingsPages(mixed $settingsPages): mixed
    {
        return self::add($settingsPages, fn ($declaration): bool => $declaration instanceof SettingsPage);
    }

    private static function add(mixed $items, callable $accepts): mixed
    {
        if (! is_array($items)) {
            return $items;
        }

        foreach (self::$declarations as $class => $declaration) {
            if ($accepts($declaration)) {
                $items[] = self::$built[$class] ??= self::build($class, $declaration);
            }
        }

        return $items;
    }

    private static function hook(): void
    {
        if (self::$hooked) {
            return;
        }

        add_filter('rwmb_meta_boxes', self::metaBoxes(...));
        add_filter('mb_settings_pages', self::settingsPages(...));
        self::$hooked = true;
    }

    private static function tooLate(): bool
    {
        return function_exists('did_filter') && did_filter('rwmb_meta_boxes') > 0 && ! doing_filter('rwmb_meta_boxes');
    }

    /**
     * Read the declaration of a class and check its attributes.
     *
     * @param  class-string  $class
     */
    private static function declaration(string $class): FieldGroup|Block|SettingsPage
    {
        if (! class_exists($class)) {
            throw new InvalidArgumentException("The class '{$class}' does not exist.");
        }

        $reflection = new ReflectionClass($class);
        $declarations = [];

        foreach ([FieldGroup::class, Block::class, SettingsPage::class] as $attribute) {
            foreach ($reflection->getAttributes($attribute) as $found) {
                $declarations[] = $found->newInstance();
            }
        }

        if (count($declarations) !== 1) {
            throw new LogicException("The class '{$class}' must be declared with one #[FieldGroup], #[Block] or #[SettingsPage] attribute.");
        }

        $declaration = $declarations[0];

        foreach ($reflection->getAttributes() as $attribute) {
            $name = $attribute->getName();

            if (self::isSetting($name) && ! self::accepts($declaration, $name)) {
                throw new LogicException("The attribute #[{$name}] of '{$class}' does not apply to a ".self::kind($declaration).'.');
            }
        }

        if ($declaration instanceof SettingsPage && $reflection->hasMethod('fields')) {
            throw new LogicException("The settings page '{$class}' cannot have fields: declare a #[FieldGroup] with #[SettingsPages({$reflection->getShortName()}::class)].");
        }

        if (! $declaration instanceof SettingsPage && ! $reflection->hasMethod('fields')) {
            throw new LogicException('The '.self::kind($declaration)." '{$class}' needs a fields() method returning its fields.");
        }

        return $declaration;
    }

    /**
     * Whether an attribute is a setting of this package, or a custom one implementing a setting contract.
     */
    private static function isSetting(string $attribute): bool
    {
        if (str_starts_with($attribute, __NAMESPACE__.'\\')) {
            return true;
        }

        return class_exists($attribute) && (is_a($attribute, FieldGroupSetting::class, true) || is_a($attribute, BlockSetting::class, true) || is_a($attribute, SettingsPageSetting::class, true));
    }

    /**
     * Whether a sub-attribute applies to a declaration.
     */
    private static function accepts(FieldGroup|Block|SettingsPage $declaration, string $attribute): bool
    {
        if (in_array($attribute, [FieldGroup::class, Block::class, SettingsPage::class], true)) {
            return true;
        }

        return match (true) {
            $declaration instanceof FieldGroup => is_a($attribute, Target::class, true) || is_a($attribute, FieldGroupSetting::class, true),
            $declaration instanceof Block => is_a($attribute, BlockSetting::class, true) || is_a($attribute, FieldGroupSetting::class, true) || $attribute === View::class,
            $declaration instanceof SettingsPage => is_a($attribute, SettingsPageSetting::class, true),
        };
    }

    private static function kind(FieldGroup|Block|SettingsPage $declaration): string
    {
        return match (true) {
            $declaration instanceof FieldGroup => 'field group',
            $declaration instanceof Block => 'block',
            $declaration instanceof SettingsPage => 'settings page',
        };
    }

    /**
     * Field groups and blocks share the Meta Box registry: an ID used twice replaces the first declaration.
     *
     * @param  class-string  $class
     */
    private static function checkId(string $class, FieldGroup|Block|SettingsPage $declaration): void
    {
        $id = self::id($class, $declaration);

        foreach (self::$declarations as $other => $registered) {
            if (($registered instanceof SettingsPage) === ($declaration instanceof SettingsPage) && self::id($other, $registered) === $id) {
                throw new LogicException("The ID '{$id}' of '{$class}' is already used by '{$other}'.");
            }
        }
    }

    /**
     * The ID of a declaration: the class name in snake case for a field group without one.
     *
     * @param  class-string  $class
     */
    private static function id(string $class, FieldGroup|Block|SettingsPage $declaration): string
    {
        if ($declaration->id !== null) {
            return $declaration->id;
        }

        $name = substr($class, (int) strrpos('\\'.$class, '\\'));

        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
    }

    /**
     * Build the settings of a declared class.
     *
     * @param  class-string  $class
     * @return array<string, mixed>
     */
    private static function build(string $class, FieldGroup|Block|SettingsPage $declaration): array
    {
        $builder = match (true) {
            $declaration instanceof Block => BlockBuilder::make($declaration->title, $declaration->id),
            $declaration instanceof SettingsPage => SettingsPageBuilder::make($declaration->title, $declaration->id),
            $declaration instanceof FieldGroup => FieldGroupBuilder::make($declaration->title, self::id($class, $declaration)),
        };

        self::$integration?->configure($builder, $class);

        $conditions = [];
        $view = null;

        foreach ((new ReflectionClass($class))->getAttributes() as $attribute) {
            if (! self::isSetting($attribute->getName())) {
                continue;
            }

            $setting = $attribute->newInstance();

            if ($setting instanceof Target) {
                $conditions[$setting->key()] = [...$conditions[$setting->key()] ?? [], ...self::slugs($class, $setting)];
            } elseif ($setting instanceof View) {
                $view = $setting->view;
            } elseif ($setting instanceof BlockSetting && $builder instanceof BlockBuilder) {
                $setting->configure($builder);
            } elseif ($setting instanceof FieldGroupSetting && $builder instanceof FieldGroupBuilder) {
                $setting->configure($builder);
            } elseif ($setting instanceof SettingsPageSetting && $builder instanceof SettingsPageBuilder) {
                $setting->configure($builder);
            }
        }

        if ($conditions !== [] && $builder instanceof FieldGroupBuilder) {
            $location = null;

            foreach ($conditions as $key => $values) {
                $location = $location === null ? Location::where($key, $values) : $location->andWhere($key, $values);
            }

            $builder->location($location);
        }

        if ($view !== null && $builder instanceof BlockBuilder) {
            self::renderWith($builder, $view, $class);
        }

        $instance = null;

        if ($builder instanceof FieldGroupBuilder) {
            $instance = self::instance($class);
            $builder->fields(call_user_func([$instance, 'fields']));
        }

        if (method_exists($class, 'configuring')) {
            call_user_func([$instance ?? self::instance($class), 'configuring'], $builder);
        }

        $settings = $builder->build();

        // The default view of the integration applies to the blocks rendered in no other way.
        if ($builder instanceof BlockBuilder && $declaration instanceof Block && ! isset($settings['render_callback']) && ! isset($settings['render_template'])) {
            $default = self::$integration?->view($class, $declaration->id);

            if ($default !== null) {
                self::renderWith($builder, $default, $class);
                $settings = $builder->build();
            }
        }

        return $settings;
    }

    /**
     * @param  class-string  $class
     */
    private static function instance(string $class): object
    {
        return self::$integration?->instance($class) ?? new $class;
    }

    /**
     * Resolve the values of a target: a class becomes the slug or ID it declares.
     *
     * @param  class-string  $class
     * @return list<string>
     */
    private static function slugs(string $class, Target $target): array
    {
        return array_map(function (string $value) use ($class, $target): string {
            if (! str_contains($value, '\\')) {
                return $value;
            }

            if (! class_exists($value)) {
                throw new InvalidArgumentException("The class '{$value}', target of '{$class}', does not exist.");
            }

            if ($target->key() === 'settings_pages') {
                $page = (new ReflectionClass($value))->getAttributes(SettingsPage::class)[0] ?? null;

                if ($page !== null) {
                    return $page->newInstance()->id;
                }
            }

            return self::$integration?->slug($value, $target->key())
                ?? throw new LogicException("The class '{$value}', target of '{$class}', does not declare any ".str_replace('_', ' ', $target->key()).'.');
        }, $target->values);
    }

    /**
     * Render the block with a view, passing the stored field values as $attributes.
     *
     * @param  class-string  $class
     */
    private static function renderWith(BlockBuilder $block, string $view, string $class): void
    {
        $integration = self::$integration
            ?? throw new LogicException("The block '{$class}' is rendered with the view '{$view}': this needs an integration rendering views, such as pollora/metabox-bridge.");

        // MB Blocks passes the arguments by name: keep these parameter names.
        $block->renderCallback(function ($attributes, $is_preview = false, $post_id = null) use ($integration, $view): void {
            echo $integration->render($view, [
                'attributes' => $attributes['data'] ?? [],
                'block' => $attributes,
                'isPreview' => (bool) $is_preview,
                'postId' => $post_id,
            ]);
        });
    }
}
