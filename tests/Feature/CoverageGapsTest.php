<?php

declare(strict_types=1);

use Pollora\Metabox\Block;
use Pollora\Metabox\Enums\Priority;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Group;
use Pollora\Metabox\Fields\Tab;
use Pollora\Metabox\Fields\Taxonomy;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Services\BlockTypeFilter;
use Pollora\Metabox\SettingsPage;
use Pollora\Metabox\Validation\EnumValidation;
use Pollora\Metabox\Validation\NumericValidation;
use Pollora\Metabox\Validation\StringValidation;

require_once __DIR__.'/../Fixtures/BlockTypeRegistry.php';

afterEach(function () {
    WP_Block_Type_Registry::$registered = [];
});

describe('block type filter', function () {
    test('leaves the list untouched when the editor context has no post', function () {
        Block::make('Gap Hero', 'gap-hero')->restrictToPostTypes(['page']);

        expect(BlockTypeFilter::filterBlockTypes(['meta-box/gap-hero'], (object) []))
            ->toBe(['meta-box/gap-hero']);
    });

    test('expands true to the registered blocks and removes the restricted one', function () {
        Block::make('Gap Cards', 'gap-cards')->restrictToPostTypes(['page']);
        WP_Block_Type_Registry::$registered = ['core/paragraph' => (object) [], 'meta-box/gap-cards' => (object) []];

        expect(BlockTypeFilter::filterBlockTypes(true, (object) ['post' => (object) ['post_type' => 'post']]))->toBe(['core/paragraph']);
    });

    test('returns a non-array value that is not true or false as is', function () {
        Block::make('Gap Quote', 'gap-quote')->restrictToPostTypes(['page']);

        expect(BlockTypeFilter::filterBlockTypes(null, (object) ['post' => (object) ['post_type' => 'post']]))->toBeNull();
    });

    test('exposes the restrictions keyed by block id and names blocks like MB Blocks', function () {
        Block::make('Gap Banner', 'gap-banner')->restrictToPostTypes(['page']);

        expect(BlockTypeFilter::getRestrictions())->toHaveKey('gap-banner')
            ->and(BlockTypeFilter::blockName('Gap Banner'))->toBe('meta-box/gap-banner');
    });
});

test('block fluent setters are chainable and stored in the built configuration', function () {
    $built = Block::make('Gap Block', 'gap-block-setters')
        ->version('1.2.3')
        ->keywords(['hero', 'banner'])
        ->enqueueAssets(fn () => null)
        ->build();

    expect($built)->toMatchArray(['version' => '1.2.3', 'keywords' => ['hero', 'banner']])
        ->and($built['enqueue_assets'])->toBeCallable();
});

test('field group type is exposed as the type setting', function () {
    expect(FieldGroup::make('Typed', 'typed_group')->type('block')->build())->toMatchArray(['type' => 'block']);
});

test('settings page help tabs are exposed in the built configuration', function () {
    $tabs = ['overview' => ['title' => 'Overview', 'content' => 'Help']];

    expect(SettingsPage::make('Gap Settings', 'gap_settings')->helpTabs($tabs)->build())
        ->toMatchArray(['help_tabs' => $tabs]);
});

test('settings page keeps an SVG icon that is already a data URI', function () {
    $uri = 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=';

    expect(SettingsPage::make('Gap Icon', 'gap_icon')->iconType('svg')->iconSvg($uri)->build()['icon_url'])->toBe($uri);
});

test('settings page encodes a raw SVG icon as a data URI', function () {
    expect(SettingsPage::make('Gap Icon Raw', 'gap_icon_raw')->iconType('svg')->iconSvg('<svg></svg>')->build()['icon_url'])
        ->toBe('data:image/svg+xml;base64,'.base64_encode('<svg></svg>'));
});

test('field exposes its id and type and accepts custom settings', function () {
    $field = Text::make('Title', 'gap_title')->setting('custom_key', 'value');

    expect($field->getId())->toBe('gap_title')
        ->and($field->getType())->toBe('text')
        ->and($field->build())->toMatchArray(['custom_key' => 'value']);
});

test('taxonomy field can keep the default meta box', function () {
    expect(Taxonomy::make('Genre', 'genre')->removeDefault(false)->build())->toMatchArray(['remove_default' => false]);
});

test('group and tab reject objects that are not renderable fields', function () {
    expect(fn () => Group::make('G', 'g')->fields([new stdClass]))->toThrow(InvalidArgumentException::class, 'Renderable')
        ->and(fn () => Tab::make('T', 't')->fields([new stdClass]))->toThrow(InvalidArgumentException::class, 'Renderable');
});

test('admin column rejects a sortable value other than true or numeric', function () {
    expect(fn () => Text::make('T', 't')->adminColumn(sortable: 'alpha'))
        ->toThrow(InvalidArgumentException::class, "'alpha' given");
});

describe('validation helpers', function () {
    test('enum validation rejects a class that does not exist', function () {
        expect(fn () => EnumValidation::validateEnumClass('Nope\\Missing'))->toThrow(InvalidArgumentException::class, 'Nope\\Missing');
        EnumValidation::validateEnumClass(Priority::class);
        expect(true)->toBeTrue();
    });

    test('numeric validation accepts numbers and any, rejects other strings', function () {
        NumericValidation::ensureIsNumeric('0.5');
        NumericValidation::ensureIsNumeric('any');

        expect(fn () => NumericValidation::ensureIsNumeric('abc'))->toThrow(InvalidArgumentException::class);
    });

    test('string validation rejects non-strings and names the enum', function () {
        StringValidation::ensureIsString('x', 'Some\\Enum');

        expect(fn () => StringValidation::ensureIsString(5, 'Some\\Enum'))->toThrow(InvalidArgumentException::class, 'Some\\Enum');
    });
});

test('settings page appends itself to the mb_settings_pages filter', function () {
    $GLOBALS['test_filters']['mb_settings_pages'] = [];
    SettingsPage::make('Gap Filter', 'gap_filter');

    $pages = apply_filters('mb_settings_pages', []);

    expect($pages)->toHaveCount(1)
        ->and($pages[0])->toMatchArray(['id' => 'gap_filter']);

    unset($GLOBALS['test_filters']['mb_settings_pages']);
});
