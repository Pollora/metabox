<?php

declare(strict_types=1);

use Pollora\Metabox\Block;
use Pollora\Metabox\Enums\Context;
use Pollora\Metabox\Enums\TextareaWrap;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Backup;
use Pollora\Metabox\Fields\CheckboxList;
use Pollora\Metabox\Fields\Column;
use Pollora\Metabox\Fields\Datepicker;
use Pollora\Metabox\Fields\DatetimePicker;
use Pollora\Metabox\Fields\Divider;
use Pollora\Metabox\Fields\Email;
use Pollora\Metabox\Fields\Group;
use Pollora\Metabox\Fields\Hidden;
use Pollora\Metabox\Fields\Icon;
use Pollora\Metabox\Fields\Post;
use Pollora\Metabox\Fields\Range;
use Pollora\Metabox\Fields\Select;
use Pollora\Metabox\Fields\SelectTree;
use Pollora\Metabox\Fields\Tab;
use Pollora\Metabox\Fields\Taxonomy;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Fields\Textarea;
use Pollora\Metabox\Fields\Timepicker;
use Pollora\Metabox\Fields\User;
use Pollora\Metabox\Fields\Wysiwyg;
use Pollora\Metabox\Location;
use Pollora\Metabox\SettingsPage;

describe('geolocation', function () {
    test('is enabled without settings', function () {
        expect(FieldGroup::make('Address', 'address')->geolocation()->build())->toHaveKey('geo', true);
    });

    test('passes the Google Maps settings', function () {
        expect(FieldGroup::make('Address', 'address')->geolocation(apiKey: 'KEY', types: ['(cities)'], countries: 'FR')->build()['geo'])
            ->toBe(['api_key' => 'KEY', 'types' => ['(cities)'], 'componentRestrictions' => ['country' => 'fr']])
            ->and(FieldGroup::make('Address', 'address')->geolocation(countries: ['fr', 'be'])->build()['geo'])
            ->toBe(['componentRestrictions' => ['country' => ['fr', 'be']]]);
    });

    test('rejects invalid countries and types', function () {
        expect(fn () => FieldGroup::make('A', 'a')->geolocation(countries: 'France'))->toThrow(InvalidArgumentException::class)
            ->and(fn () => FieldGroup::make('A', 'a')->geolocation(countries: ['fr', 'be', 'de', 'it', 'es', 'pt']))->toThrow(InvalidArgumentException::class)
            ->and(fn () => FieldGroup::make('A', 'a')->geolocation(types: ['']))->toThrow(InvalidArgumentException::class);
    });

    test('binds fields to address components', function () {
        expect(Text::make('City', 'city_ho')->geoBinding('locality')->addressField('address_ho')->build())
            ->toMatchArray(['binding' => 'locality', 'address_field' => 'address_ho'])
            ->not->toHaveKey('bind_if_empty')
            ->and(Hidden::make('Country', 'country_code')->geoBinding('short:country', bindIfEmpty: false)->build())
            ->toMatchArray(['binding' => 'short:country', 'bind_if_empty' => false])
            ->and(fn () => Text::make('City', 'city')->geoBinding(' '))->toThrow(InvalidArgumentException::class);
    });

    test('is available on input, textarea, hidden and select fields', function () {
        foreach ([Textarea::class, Select::class, Email::class] as $class) {
            expect($class::make('Field', 'field')->geoBinding('route')->build())->toHaveKey('binding', 'route');
        }
    });
});

test('seoAnalysis() adds the field to the Yoast SEO and Rank Math analysis', function () {
    foreach ([Text::class, Textarea::class, Wysiwyg::class] as $class) {
        expect($class::make('Intro', 'intro')->seoAnalysis()->build())
            ->toMatchArray(['add_to_wpseo_analysis' => true, 'rank_math_analysis' => true]);
    }
});

describe('columns', function () {
    test('group several fields in a column', function () {
        $metabox = FieldGroup::make('Contact', 'contact')->fields([
            Column::make(4)->fields([Text::make('Name', 'name'), Email::make('Email', 'email')]),
            Column::make(8)->class('highlight')->fields([Textarea::make('Message', 'message'), Divider::make()]),
            Text::make('Notes', 'notes')->columns(6),
        ])->build();

        expect($metabox['columns'])->toBe(['column-1' => 4, 'column-2' => ['size' => 8, 'class' => 'highlight']])
            ->and(array_map(fn ($field) => $field['column'] ?? null, $metabox['fields']))
            ->toBe(['column-1', 'column-1', 'column-2', 'column-2', null])
            ->and($metabox['fields'][4]['columns'])->toBe(6);
    });

    test('work in tabs', function () {
        $metabox = FieldGroup::make('Contact', 'contact')->fields([
            Tab::make('General', 'general')->fields([
                Column::make(6)->fields([Text::make('Name', 'name')]),
                Column::make(6)->fields([Text::make('Phone', 'phone')]),
            ]),
        ])->build();

        expect($metabox['fields'][1])->toMatchArray(['id' => 'phone', 'tab' => 'general', 'column' => 'column-2']);
    });

    test('reject invalid sizes and content', function () {
        expect(fn () => Column::make(13))->toThrow(InvalidArgumentException::class)
            ->and(fn () => Column::make(6)->fields([Tab::make('Tab', 'tab')]))->toThrow(InvalidArgumentException::class)
            ->and(fn () => Column::make(6)->fields([Column::make(6)]))->toThrow(InvalidArgumentException::class)
            ->and(fn () => Group::make('Group', 'group')->fields([Column::make(6)]))->toThrow(InvalidArgumentException::class);
    });
});

describe('customizer', function () {
    test('displays a meta box as a top-level section or in a panel', function () {
        expect(FieldGroup::make('General', 'general')->customizer()->priority(30)->build())
            ->toMatchArray(['panel' => '', 'priority' => 30])
            ->not->toHaveKey('post_types')
            ->and(FieldGroup::make('General', 'general')->customizer('theme_panel', optionName: 'footer')->build())
            ->toMatchArray(['panel' => 'theme_panel', 'option_name' => 'footer']);
    });

    test('accepts an integer priority on settings pages only', function () {
        expect(FieldGroup::make('General', 'general')->location(Location::settingsPages('theme'))->priority(10)->build())
            ->toHaveKey('priority', 10)
            ->and(fn () => FieldGroup::make('General', 'general')->priority(10)->build())->toThrow(LogicException::class);
    });
});

test('hideFromBlockBindings(), autofocus() and fieldName() work on every field', function () {
    expect(Text::make('Title', 'title')->hideFromBlockBindings()->autofocus()->fieldName('custom[title]')->build())
        ->toMatchArray(['hide_from_block_bindings' => true, 'autofocus' => true, 'field_name' => 'custom[title]']);
});

describe('input tooltip', function () {
    test('is set like the label tooltip', function () {
        expect(Text::make('Title', 'title')->inputTooltip('Help')->build())->toHaveKey('tooltip_input', 'Help')
            ->and(Select::make('Type', 'type')->inputTooltip('Help', position: 'right')->build()['tooltip_input'])
            ->toBe(['content' => 'Help', 'position' => 'right'])
            ->and(Timepicker::make('Time', 'time')->inputTooltip('Help')->build())->toHaveKey('tooltip_input');
    });

    test('rejects the field types Meta Box Tooltip does not support', function () {
        expect(fn () => Icon::make('Icon', 'icon')->inputTooltip('Help'))->toThrow(LogicException::class)
            ->and(fn () => Text::make('Phone', 'phone')->type('tel')->inputTooltip('Help'))->toThrow(LogicException::class)
            ->and(fn () => Text::make('Phone', 'phone')->inputTooltip('Help')->type('tel'))->toThrow(LogicException::class);
    });
});

describe('trees', function () {
    $tree = [
        'europe' => ['label' => 'Europe', 'children' => ['fr' => 'France', 'be' => 'Belgium']],
        'asia' => 'Asia',
    ];
    $options = [
        ['value' => 'europe', 'label' => 'Europe'],
        ['value' => 'fr', 'label' => 'France', 'parent' => 'europe'],
        ['value' => 'be', 'label' => 'Belgium', 'parent' => 'europe'],
        ['value' => 'asia', 'label' => 'Asia'],
    ];

    test('SelectTree builds hierarchical options', function () use ($tree, $options) {
        expect(SelectTree::make('Region', 'region')->tree($tree)->build())
            ->toMatchArray(['type' => 'select_tree', 'options' => $options, 'flatten' => false]);
    });

    test('CheckboxList builds a checkbox tree', function () use ($tree, $options) {
        expect(CheckboxList::make('Regions', 'regions')->tree($tree)->collapse(false)->build())
            ->toMatchArray(['type' => 'checkbox_list', 'options' => $options, 'flatten' => false, 'collapse' => false]);
    });

    test('rejects malformed trees', function () {
        expect(fn () => SelectTree::make('R', 'r')->tree(['a' => ['children' => []]]))->toThrow(InvalidArgumentException::class)
            ->and(fn () => SelectTree::make('R', 'r')->tree(['a' => ['label' => 'A', 'icon' => 'x']]))->toThrow(InvalidArgumentException::class)
            ->and(fn () => SelectTree::make('R', 'r')->tree(['a' => ['label' => 'A', 'children' => ['a' => 'A']]]))->toThrow(InvalidArgumentException::class)
            ->and(fn () => SelectTree::make('R', 'r')->tree(['a' => 1]))->toThrow(InvalidArgumentException::class);
    });
});

test('Backup has default name and ID', function () {
    expect(Backup::make()->rows(10)->build())->toMatchArray(['type' => 'backup', 'name' => 'Backup', 'id' => 'backup', 'rows' => 10]);
});

describe('object fields', function () {
    test('addNew() and toggleAllButton()', function () {
        expect(Post::make('Speaker', 'speaker')->postType('speaker')->addNew()->toggleAllButton()->build())
            ->toMatchArray(['add_new' => true, 'select_all_none' => true])
            ->and(User::make('Author', 'author')->addNew()->build())->toHaveKey('add_new', true)
            ->and(Taxonomy::make('Tags', 'tags')->toggleAllButton()->build())->toHaveKey('select_all_none', true);
    });

    test('Post::addNew() requires a single post type', function () {
        expect(Post::make('Item', 'item')->addNew()->build())->toHaveKey('add_new', true)
            ->and(fn () => Post::make('Item', 'item')->postType(['post', 'page'])->addNew()->build())->toThrow(LogicException::class);
    });
});

test('date fields set their display format and autocomplete', function () {
    expect(Datepicker::make('Date', 'date')->dateFormat('dd/mm/yy')->autocomplete('on')->build())
        ->toMatchArray(['js_options' => ['dateFormat' => 'dd/mm/yy'], 'autocomplete' => 'on'])
        ->and(DatetimePicker::make('Start', 'start')->dateFormat('dd/mm/yy')->timeFormat('HH:mm:ss')->jsOptions(['stepMinute' => 15])->build()['js_options'])
        ->toBe(['dateFormat' => 'dd/mm/yy', 'timeFormat' => 'HH:mm:ss', 'stepMinute' => 15]);
});

test('Textarea has the input settings and wrap()', function () {
    expect(Textarea::make('Bio', 'bio')->minLength(10)->maxLength(500)->autocomplete('off')->wrap(TextareaWrap::Hard)->build())
        ->toMatchArray(['minlength' => 10, 'maxlength' => 500, 'autocomplete' => 'off', 'wrap' => 'hard'])
        ->and(fn () => Textarea::make('Bio', 'bio')->wrap('virtual'))->toThrow(InvalidArgumentException::class);
});

test('Wysiwyg can disable the distraction-free writing mode', function () {
    expect(Wysiwyg::make('Content', 'content')->distractionFreeWriting(false)->build()['options'])->toBe(['dfw' => false]);
});

describe('block attributes', function () {
    test('are added to the MB Blocks default attributes', function () {
        $block = Block::make('Hero', 'hero')->preview(['title' => 'Hello'])->attributes(['theme' => ['type' => 'string', 'default' => 'light']])->build();

        expect($block['attributes'])->toBe([
            'id' => ['type' => 'string', 'default' => 'hero'],
            'name' => ['type' => 'string', 'default' => 'hero'],
            'data' => ['type' => 'object', 'default' => ['title' => 'Hello']],
            'anchor' => ['type' => 'string', 'default' => ''],
            'theme' => ['type' => 'string', 'default' => 'light'],
        ]);
    });

    test('are not set by default', function () {
        expect(Block::make('Hero', 'hero')->build())->not->toHaveKey('attributes');
    });

    test('are validated', function () {
        expect(fn () => Block::make('Hero', 'hero')->attributes(['data' => ['type' => 'object']]))->toThrow(InvalidArgumentException::class)
            ->and(fn () => Block::make('Hero', 'hero')->attributes(['theme' => ['default' => 'light']]))->toThrow(InvalidArgumentException::class)
            ->and(fn () => Block::make('Hero', 'hero')->attributes(['theme' => 'string']))->toThrow(InvalidArgumentException::class);
    });
});

describe('settings page tabs', function () {
    test('tab() adds tabs with an optional icon', function () {
        expect(SettingsPage::make('Theme', 'theme')->tab('general', 'General')->tab('style', 'Style', icon: 'dashicons-art')->build()['tabs'])
            ->toBe(['general' => 'General', 'style' => ['label' => 'Style', 'icon' => 'dashicons-art']])
            ->and(fn () => SettingsPage::make('Theme', 'theme')->tab('general', 'General')->tab('general', 'Other'))->toThrow(InvalidArgumentException::class);
    });

    test('Location::settingsPages() takes the tab', function () {
        expect(Location::settingsPages('theme', tab: 'style')->get())->toBe(['settings_pages' => ['theme'], 'tab' => 'style'])
            ->and(Location::settingsPages('theme')->get())->toBe(['settings_pages' => ['theme']]);
    });
});

test('context() accepts the Context enum', function () {
    expect(FieldGroup::make('Side', 'side')->context(Context::Side)->build())->toHaveKey('context', 'side');
});

test('Range fields have no input tooltip', function () {
    expect(method_exists(Range::class, 'inputTooltip'))->toBeFalse();
});
