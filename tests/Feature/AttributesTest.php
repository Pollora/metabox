<?php

declare(strict_types=1);

use Pollora\Metabox\Attributes\Contracts\Integration;
use Pollora\Metabox\Attributes\Registrar;
use Pollora\Metabox\Block;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\SettingsPage;
use Tests\Fixtures\Attributes\CustomSetting;
use Tests\Fixtures\Attributes\DuplicateId;
use Tests\Fixtures\Attributes\Event;
use Tests\Fixtures\Attributes\FooterOptions;
use Tests\Fixtures\Attributes\Plain;
use Tests\Fixtures\Attributes\PlainBlock;
use Tests\Fixtures\Attributes\Quote;
use Tests\Fixtures\Attributes\SettingsPageWithFields;
use Tests\Fixtures\Attributes\SolutionFields;
use Tests\Fixtures\Attributes\TemplateBlock;
use Tests\Fixtures\Attributes\ThemeOptions;
use Tests\Fixtures\Attributes\TwoDeclarations;
use Tests\Fixtures\Attributes\UnresolvedTarget;
use Tests\Fixtures\Attributes\ViewWithoutIntegration;
use Tests\Fixtures\Attributes\WithoutFields;
use Tests\Fixtures\Attributes\WrongSetting;

require_once __DIR__.'/../Fixtures/Attributes.php';

/**
 * An integration recording what it renders, resolving Event as the 'event' post type.
 */
function testIntegration(): Integration
{
    return new class implements Integration
    {
        public array $rendered = [];

        public function instance(string $class): object
        {
            return new $class;
        }

        public function configure(FieldGroup|SettingsPage $builder, string $class): void
        {
            if ($builder instanceof Block) {
                $builder->category('site')->mode('edit');
            }
        }

        public function slug(string $class, string $key): ?string
        {
            return $class === Event::class && $key === 'post_types' ? 'event' : null;
        }

        public function view(string $class, string $blockId): ?string
        {
            return 'blocks.'.$blockId;
        }

        public function render(string $view, array $data): string
        {
            $this->rendered[] = [$view, $data];

            return "<{$view}>";
        }
    };
}

/**
 * Call a render callback the way MB Blocks does: arguments are matched by parameter name.
 */
function resolveByName(callable $callback, array $bind): void
{
    $arguments = array_map(fn (ReflectionParameter $parameter) => $bind[$parameter->name] ?? null, (new ReflectionFunction($callback(...)))->getParameters());

    $callback(...$arguments);
}

function metaBoxes(): array
{
    return apply_filters('rwmb_meta_boxes', []);
}

beforeEach(function () {
    Registrar::reset();
    $GLOBALS['test_filters'] = [];
    $GLOBALS['test_did_filters'] = [];
    SolutionFields::$instances = 0;
});

test('a field group is built from its attributes, fields and configuring method', function () {
    Registrar::register(SolutionFields::class);

    expect(metaBoxes())->toHaveCount(1)
        ->and(metaBoxes()[0])->toMatchArray([
            'id' => 'solution_fields',
            'title' => 'Solution fields',
            'post_types' => ['solution', 'page'],
            'taxonomies' => ['category'],
            'priority' => 'high',
        ])
        ->and(metaBoxes()[0]['fields'][0]['id'])->toBe('color');
});

test('a class is registered and built once', function () {
    Registrar::register(SolutionFields::class, SolutionFields::class);
    Registrar::register(SolutionFields::class);

    expect(SolutionFields::$instances)->toBe(0);

    metaBoxes();
    metaBoxes();

    expect(metaBoxes())->toHaveCount(1)
        ->and(SolutionFields::$instances)->toBe(1)
        ->and($GLOBALS['test_filters']['rwmb_meta_boxes'])->toHaveCount(1);
});

test('the builders are not registered a second time when built in the filter', function () {
    Registrar::register(Plain::class);
    metaBoxes();

    expect($GLOBALS['test_filters']['rwmb_meta_boxes'])->toHaveCount(1);
});

test('a block gets the integration defaults, then its attributes', function () {
    Registrar::integrate(testIntegration());
    Registrar::register(Quote::class);

    expect(metaBoxes()[0])->toMatchArray([
        'id' => 'quote',
        'type' => 'block',
        'icon' => 'format-quote',
        'description' => 'A quote with its author',
        'category' => 'theme',
        'mode' => 'preview',
    ]);
});

test('a block view receives the stored values, the block attributes and the context', function () {
    $integration = testIntegration();
    Registrar::integrate($integration);
    Registrar::register(Quote::class, PlainBlock::class);

    [$quote, $plain] = metaBoxes();

    ob_start();
    resolveByName($quote['render_callback'], [
        'attributes' => ['data' => ['quote' => 'Hello'], 'anchor' => 'top'],
        'is_preview' => true,
        'post_id' => 12,
    ]);
    resolveByName($plain['render_callback'], ['attributes' => []]);

    expect(ob_get_clean())->toBe('<blocks.quote><blocks.plain-block>')
        ->and($integration->rendered[0])->toBe(['blocks.quote', [
            'attributes' => ['quote' => 'Hello'],
            'block' => ['data' => ['quote' => 'Hello'], 'anchor' => 'top'],
            'isPreview' => true,
            'postId' => 12,
        ]]);
});

test('a settings page is built for MB Settings Page', function () {
    Registrar::register(ThemeOptions::class);

    expect(metaBoxes())->toBe([])
        ->and(apply_filters('mb_settings_pages', []))->toHaveCount(1)
        ->and(apply_filters('mb_settings_pages', [])[0])->toMatchArray([
            'id' => 'theme-options',
            'page_title' => 'Theme options',
            'position' => 2,
            'option_name' => 'theme',
            'submit_button' => 'Save',
        ]);
});

test('a class used as a target becomes the ID or slug it declares', function () {
    Registrar::integrate(testIntegration());
    Registrar::register(FooterOptions::class, ThemeOptions::class);

    expect(metaBoxes()[0])->toMatchArray([
        'settings_pages' => ['theme-options'],
        'post_types' => ['event'],
    ]);
});

test('a custom attribute implementing a setting contract is applied', function () {
    Registrar::register(CustomSetting::class);

    expect(metaBoxes()[0]['closed'])->toBeTrue();
});

test('invalid declarations are rejected when registered', function (string $class, string $message) {
    Registrar::register(SolutionFields::class);

    expect(fn () => Registrar::register($class))->toThrow(LogicException::class, $message);
})->with([
    'ID used twice' => [DuplicateId::class, "The ID 'solution_fields' of 'Tests\\Fixtures\\Attributes\\DuplicateId' is already used by 'Tests\\Fixtures\\Attributes\\SolutionFields'."],
    'no fields method' => [WithoutFields::class, 'needs a fields() method'],
    'two declarations' => [TwoDeclarations::class, 'must be declared with one #[FieldGroup], #[Block] or #[SettingsPage] attribute'],
    'setting of another kind' => [WrongSetting::class, 'does not apply to a field group'],
    'fields on a settings page' => [SettingsPageWithFields::class, 'cannot have fields: declare a #[FieldGroup] with #[SettingsPages(SettingsPageWithFields::class)]'],
]);

test('an unknown class is rejected', function () {
    expect(fn () => Registrar::register('App\\Missing'))->toThrow(InvalidArgumentException::class, "The class 'App\\Missing' does not exist.");
});

test('a target class declaring nothing is rejected when built', function () {
    Registrar::integrate(testIntegration());
    Registrar::register(UnresolvedTarget::class);

    expect(fn () => metaBoxes())->toThrow(LogicException::class, "The class 'Tests\\Fixtures\\Attributes\\Plain', target of 'Tests\\Fixtures\\Attributes\\UnresolvedTarget', does not declare any post types.");
});

test('a block view needs an integration', function () {
    Registrar::register(ViewWithoutIntegration::class);

    expect(fn () => metaBoxes())->toThrow(LogicException::class, "is rendered with the view 'blocks.unrendered'");
});

test('a field group registered after Meta Box read the declarations is rejected', function () {
    $GLOBALS['test_did_filters'] = ['rwmb_meta_boxes'];

    expect(fn () => Registrar::register(Plain::class))->toThrow(LogicException::class, 'is registered too late');
});

test('the default view does not replace a template set in configuring()', function () {
    Registrar::integrate(testIntegration());
    Registrar::register(TemplateBlock::class);

    expect(metaBoxes()[0])->toHaveKey('render_template', 'blocks/template.php')
        ->not->toHaveKey('render_callback');
});
