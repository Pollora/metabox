<?php

declare(strict_types=1);

use Pollora\Metabox\Attributes\Contracts\Integration;
use Pollora\Metabox\Attributes\Registrar;
use Pollora\Metabox\AttributesExtra\WithMarker;
use Pollora\Metabox\Block;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\SettingsPage;
use Tests\Fixtures\Attributes\BlockWithoutFields;
use Tests\Fixtures\Attributes\BlockWithPageSetting;
use Tests\Fixtures\Attributes\CamelCaseName;
use Tests\Fixtures\Attributes\ClosedBlock;
use Tests\Fixtures\Attributes\ConfiguredBlock;
use Tests\Fixtures\Attributes\CustomBlock;
use Tests\Fixtures\Attributes\CustomPage;
use Tests\Fixtures\Attributes\CustomSetting;
use Tests\Fixtures\Attributes\DualGroup;
use Tests\Fixtures\Attributes\DualPage;
use Tests\Fixtures\Attributes\DuplicateId;
use Tests\Fixtures\Attributes\DuplicatePage;
use Tests\Fixtures\Attributes\Event;
use Tests\Fixtures\Attributes\EventTarget;
use Tests\Fixtures\Attributes\FooterOptions;
use Tests\Fixtures\Attributes\ForeignFirst;
use Tests\Fixtures\Attributes\GroupedBlock;
use Tests\Fixtures\Attributes\GroupSharingPageId;
use Tests\Fixtures\Attributes\LatePage;
use Tests\Fixtures\Attributes\MultiTargets;
use Tests\Fixtures\Attributes\PageWithTarget;
use Tests\Fixtures\Attributes\Plain;
use Tests\Fixtures\Attributes\PlainBlock;
use Tests\Fixtures\Attributes\Prebuilt;
use Tests\Fixtures\Attributes\Quote;
use Tests\Fixtures\Attributes\SettingsPageWithFields;
use Tests\Fixtures\Attributes\SolutionFields;
use Tests\Fixtures\Attributes\TemplateBlock;
use Tests\Fixtures\Attributes\ThemeOptions;
use Tests\Fixtures\Attributes\TwoDeclarations;
use Tests\Fixtures\Attributes\UnresolvedTarget;
use Tests\Fixtures\Attributes\ViewWithoutIntegration;
use Tests\Fixtures\Attributes\WithForeignAttribute;
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

/**
 * The message of the exception thrown by a callback.
 */
function thrownMessage(callable $callback): string
{
    try {
        $callback();
    } catch (Throwable $e) {
        return $e->getMessage();
    }

    return '';
}

/**
 * An integration handing out a prepared instance, with no view and no slug.
 */
function instanceIntegration(object $instance): Integration
{
    return new class($instance) implements Integration
    {
        public function __construct(private readonly object $instance) {}

        public function instance(string $class): object
        {
            return $this->instance;
        }

        public function configure(FieldGroup|SettingsPage $builder, string $class): void {}

        public function slug(string $class, string $key): ?string
        {
            return null;
        }

        public function view(string $class, string $blockId): ?string
        {
            return null;
        }

        public function render(string $view, array $data): string
        {
            return '';
        }
    };
}

test('a class registered twice does not stop the next ones from being registered', function () {
    Registrar::register(Plain::class, Plain::class, SolutionFields::class);

    expect(array_keys(Registrar::registered()))->toBe([Plain::class, SolutionFields::class]);
});

test('a settings page can be registered after Meta Box read the declarations', function () {
    $GLOBALS['test_did_filters'] = ['rwmb_meta_boxes'];

    Registrar::register(LatePage::class);

    expect(Registrar::registered())->toHaveKey(LatePage::class);
});

test('field groups and settings pages are given to their own filter only', function () {
    Registrar::register(SolutionFields::class, ThemeOptions::class);

    expect(array_column(metaBoxes(), 'id'))->toBe(['solution_fields'])
        ->and(array_column(apply_filters('mb_settings_pages', []), 'id'))->toBe(['theme-options']);
});

test('the filters leave what is not a list untouched', function () {
    Registrar::register(SolutionFields::class);

    expect(Registrar::metaBoxes('x'))->toBe('x')
        ->and(Registrar::settingsPages(null))->toBeNull();
});

test('the messages of the invalid attribute declarations are complete', function () {
    expect(thrownMessage(fn () => Registrar::register(WrongSetting::class)))
        ->toBe("The attribute #[Pollora\\Metabox\\Attributes\\Block\\Icon] of 'Tests\\Fixtures\\Attributes\\WrongSetting' does not apply to a field group.")
        ->and(thrownMessage(fn () => Registrar::register(BlockWithPageSetting::class)))
        ->toBe("The attribute #[Pollora\\Metabox\\Attributes\\SettingsPage\\Position] of 'Tests\\Fixtures\\Attributes\\BlockWithPageSetting' does not apply to a block.")
        ->and(thrownMessage(fn () => Registrar::register(PageWithTarget::class)))
        ->toBe("The attribute #[Pollora\\Metabox\\Attributes\\FieldGroup\\PostTypes] of 'Tests\\Fixtures\\Attributes\\PageWithTarget' does not apply to a settings page.");
});

test('the message of a missing fields method names the kind of declaration', function () {
    expect(thrownMessage(fn () => Registrar::register(WithoutFields::class)))
        ->toBe("The field group 'Tests\\Fixtures\\Attributes\\WithoutFields' needs a fields() method returning its fields.")
        ->and(thrownMessage(fn () => Registrar::register(BlockWithoutFields::class)))
        ->toBe("The block 'Tests\\Fixtures\\Attributes\\BlockWithoutFields' needs a fields() method returning its fields.");
});

test('an attribute of a namespace starting like the package one is not a setting', function () {
    require_once __DIR__.'/../Fixtures/AttributesExtra.php';

    Registrar::register(WithMarker::class);

    expect(Registrar::registered())->toHaveKey(WithMarker::class);
});

test('an unrelated attribute of an existing class is not a setting', function () {
    Registrar::register(WithForeignAttribute::class);

    expect(metaBoxes()[0]['id'])->toBe('allow_dynamic');
});

test('custom settings are applied to the kind of declaration they implement', function () {
    Registrar::register(CustomBlock::class, DualGroup::class, GroupedBlock::class, CustomPage::class, DualPage::class);

    $blocks = array_column(metaBoxes(), null, 'id');
    $pages = array_column(apply_filters('mb_settings_pages', []), null, 'id');

    expect($blocks['custom-block']['category'])->toBe('custom-category')
        ->and($blocks['dual_group']['closed'])->toBeTrue()
        ->and($blocks)->toHaveKey('grouped-block')
        ->and($pages['plain-page']['position'])->toBe(9)
        ->and($pages['custom-page']['position'])->toBe(7);
});

test('an ID is unique among the settings pages, but shared by a page and a field group', function () {
    Registrar::register(ThemeOptions::class, GroupSharingPageId::class);

    expect(array_keys(Registrar::registered()))->toBe([ThemeOptions::class, GroupSharingPageId::class])
        ->and(thrownMessage(fn () => Registrar::register(DuplicatePage::class)))
        ->toBe("The ID 'theme-options' of 'Tests\\Fixtures\\Attributes\\DuplicatePage' is already used by 'Tests\\Fixtures\\Attributes\\ThemeOptions'.");
});

test('a field group without an ID gets its class name in snake case', function () {
    Registrar::register(CamelCaseName::class);

    expect(metaBoxes()[0]['id'])->toBe('camel_case_name');
});

test('the targets of the same kind are merged', function () {
    Registrar::register(MultiTargets::class);

    expect(metaBoxes()[0])->toMatchArray(['post_types' => ['post', 'page', 'book'], 'taxonomies' => ['genre']]);
});

test('a block without a view and without integration has no render callback', function () {
    Registrar::register(PlainBlock::class);

    expect(metaBoxes()[0])->not->toHaveKey('render_callback');
});

test('a class target needs an integration to be resolved', function () {
    Registrar::register(EventTarget::class);

    expect(thrownMessage(fn () => metaBoxes()))
        ->toBe("The class 'Tests\\Fixtures\\Attributes\\Event', target of 'Tests\\Fixtures\\Attributes\\EventTarget', does not declare any post types.");
});

test('the instance of the integration is the one used for fields and configuring', function () {
    Registrar::integrate(instanceIntegration(new Prebuilt('from integration')));
    Registrar::register(Prebuilt::class);

    expect(metaBoxes()[0]['fields'][0]['name'])->toBe('from integration');

    Registrar::reset();
    Registrar::integrate(instanceIntegration(new ConfiguredBlock('integration-category')));
    Registrar::register(ConfiguredBlock::class);

    expect(metaBoxes()[0]['category'])->toBe('integration-category');
});

test('the render callback is not a preview by default and casts the flag', function () {
    $integration = testIntegration();
    Registrar::integrate($integration);
    Registrar::register(Quote::class);

    $callback = metaBoxes()[0]['render_callback'];

    ob_start();
    $callback(['data' => []]);
    $callback(['data' => []], 1, 5);
    ob_end_clean();

    expect($integration->rendered[0][1]['isPreview'])->toBeFalse()
        ->and($integration->rendered[0][1]['postId'])->toBeNull()
        ->and($integration->rendered[1][1]['isPreview'])->toBeTrue()
        ->and($integration->rendered[1][1]['postId'])->toBe(5);
});

test('a block accepts the settings of a field group', function () {
    Registrar::register(ClosedBlock::class);

    expect(metaBoxes()[0]['closed'])->toBeTrue();
});

test('the settings after an attribute that is not one are still applied', function () {
    Registrar::register(ForeignFirst::class);

    expect(metaBoxes()[0]['post_types'])->toBe(['page']);
});

test('an instance of the integration whose fields are not an array is reported', function () {
    Registrar::integrate(instanceIntegration(new class
    {
        public function fields(): string
        {
            return 'none';
        }
    }));
    Registrar::register(Prebuilt::class);

    expect(fn () => metaBoxes())->toThrow(LogicException::class, 'must return an array of fields');
});
