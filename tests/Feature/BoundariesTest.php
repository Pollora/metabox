<?php

declare(strict_types=1);

use Pollora\Metabox\Block;
use Pollora\Metabox\Enums\AdminColumnLink;
use Pollora\Metabox\Enums\BoxStyle;
use Pollora\Metabox\Enums\Context;
use Pollora\Metabox\Enums\IconType;
use Pollora\Metabox\Enums\ModelSupport;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Column;
use Pollora\Metabox\Fields\Group;
use Pollora\Metabox\Fields\Number;
use Pollora\Metabox\Fields\Range;
use Pollora\Metabox\Fields\SelectTree;
use Pollora\Metabox\Fields\Slider;
use Pollora\Metabox\Fields\Tab;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Location;
use Pollora\Metabox\MetaboxModel;
use Pollora\Metabox\Relationship;
use Pollora\Metabox\Relationships\Side;
use Pollora\Metabox\Rules\Rule;
use Pollora\Metabox\Services\BlockTypeFilter;
use Pollora\Metabox\SettingsPage;

/**
 * The message of the exception thrown by a callback.
 */
function messageOf(callable $callback): string
{
    try {
        $callback();
    } catch (Throwable $e) {
        return $e->getMessage();
    }

    return '';
}

function setStatic(string $class, string $property, mixed $value): mixed
{
    $reflection = new ReflectionProperty($class, $property);
    $previous = $reflection->getValue();
    $reflection->setValue(null, $value);

    return $previous;
}

afterEach(function () {
    unset($GLOBALS['test_did_actions'], $GLOBALS['test_doing_actions'], $GLOBALS['test_actions']);
});

describe('grid sizes', function () {
    test('a field spans 1 to 12 columns', function (int $columns) {
        expect(Text::make('A', 'a')->columns($columns)->build())->toHaveKey('columns', $columns);
    })->with([1, 2, 11, 12]);

    test('a field cannot span less than 1 or more than 12 columns', function (int $columns) {
        expect(messageOf(fn () => Text::make('A', 'a')->columns($columns)))
            ->toBe("A field spans 1 to 12 columns, {$columns} given.");
    })->with([0, -1, 13]);

    test('a column spans 1 to 12 grid columns', function (int $size) {
        expect(Column::make($size)->build())->toBe($size);
    })->with([1, 2, 11, 12]);

    test('a column cannot span less than 1 or more than 12 grid columns', function (int $size) {
        expect(messageOf(fn () => Column::make($size)))
            ->toBe("A column spans 1 to 12 grid columns, {$size} given.");
    })->with([0, -1, 13]);
});

describe('field groups', function () {
    beforeEach(function () {
        $GLOBALS['test_filters'] = [];
    });

    test('a field group registers itself on rwmb_meta_boxes', function () {
        FieldGroup::make('Details', 'details')->fields([Text::make('A', 'a')]);

        expect($GLOBALS['test_filters']['rwmb_meta_boxes'])->toHaveCount(1)
            ->and(array_column(apply_filters('rwmb_meta_boxes', []), 'id'))->toBe(['details']);
    });

    test('geolocation accepts 5 countries and refuses 6', function () {
        $group = FieldGroup::make('A', 'a')->geolocation(countries: ['fr', 'be', 'de', 'es', 'it']);

        expect($group->build()['geo']['componentRestrictions']['country'])->toBe(['fr', 'be', 'de', 'es', 'it'])
            ->and(messageOf(fn () => FieldGroup::make('B', 'b')->geolocation(countries: ['fr', 'be', 'de', 'es', 'it', 'pt'])))
            ->toBe('Google Maps restricts the suggestions to 5 countries at most.');
    });

    test('geolocation gives the place types as a list', function () {
        expect(FieldGroup::make('A', 'a')->geolocation(types: [3 => 'restaurant', 7 => 'cafe'])->build()['geo']['types'])
            ->toBe(['restaurant', 'cafe']);
    });

    test('models of a field group must share the same table', function () {
        MetaboxModel::make('first_model')->table('first_table');
        MetaboxModel::make('second_model')->table('second_table');

        $group = FieldGroup::make('A', 'a')->location(Location::models(['first_model', 'second_model']));

        expect(messageOf(fn () => $group->build()))
            ->toBe('The models of a field group must share the same table: wp_first_table, wp_second_table.');
    });

    test('models given as one name are resolved', function () {
        MetaboxModel::make('single_model')->table('single_table');

        expect(FieldGroup::make('A', 'a')->location(Location::where('models', 'single_model'))->build())
            ->toMatchArray(['storage_type' => 'custom_table', 'table' => 'wp_single_table']);
    });

    test('a location of models is a list', function () {
        expect(Location::models('one_model')->get())->toBe(['models' => ['one_model']]);
    });

    test('fields that are not renderable are ignored', function () {
        expect(FieldGroup::make('A', 'a')->fields([new stdClass, Text::make('A', 'a')])->build()['fields'])->toHaveCount(1);
    });

    test('fields of a tab that are not renderable are ignored', function () {
        $tab = Tab::make('Tab', 'tab')->fields([Text::make('A', 'a')]);

        expect(FieldGroup::make('B', 'b')->fields([$tab])->build()['fields'])->toHaveCount(1);
    });

    test('the fields of a column outside a tab have no tab', function () {
        $fields = FieldGroup::make('A', 'a')->fields([Column::make(6)->fields([Text::make('A', 'a')])])->build()['fields'];

        expect($fields[0])->not->toHaveKey('tab')
            ->and($fields[0]['column'])->toBe('column-1');
    });

    test('a group refuses a column and a non renderable field', function () {
        expect(messageOf(fn () => Group::make('G', 'g')->fields([Column::make(6)])))
            ->toBe('Columns are only supported in meta boxes and tabs: use columns() on the fields of the group.')
            ->and(messageOf(fn () => Group::make('G', 'g')->fields([new stdClass])))
            ->toBe('All fields must implement Renderable.');
    });
});

describe('blocks', function () {
    test('every attribute type is accepted', function (string $type) {
        expect(Block::make('B', 'b')->attributes(['x' => ['type' => $type]])->build()['attributes'])->toHaveKey('x');
    })->with(['string', 'number', 'integer', 'boolean', 'object', 'array', 'null']);

    test('an unknown attribute type is refused with the list of types', function () {
        expect(messageOf(fn () => Block::make('B', 'b')->attributes(['x' => ['type' => 'float']])))
            ->toBe("The block attribute 'x' needs a type: 'string', 'number', 'integer', 'boolean', 'object', 'array', 'null'.")
            ->and(messageOf(fn () => Block::make('B', 'b')->attributes(['x' => 'string'])))
            ->toContain("The block attribute 'x' needs a type");
    });

    test('the attributes reserved by MB Blocks are refused', function (string $name) {
        expect(messageOf(fn () => Block::make('B', 'b')->attributes([$name => ['type' => 'string']])))
            ->toBe("The block attribute '{$name}' is reserved by MB Blocks.");
    })->with(['id', 'name', 'data', 'anchor']);
});

describe('block type filter', function () {
    test('the filter is added once, late, with the two arguments', function () {
        $restrictions = setStatic(BlockTypeFilter::class, 'restrictions', []);
        $added = setStatic(BlockTypeFilter::class, 'filterAdded', false);
        $GLOBALS['test_filter_registrations'] = [];

        try {
            BlockTypeFilter::registerRestrictions('one', ['allowed' => ['page']]);
            BlockTypeFilter::registerRestrictions('two', ['allowed' => ['post']]);

            $mine = array_values(array_filter($GLOBALS['test_filter_registrations'], fn ($r) => $r[0] === 'allowed_block_types_all'));

            expect($mine)->toBe([['allowed_block_types_all', 99999, 2]])
                ->and($GLOBALS['test_filters']['allowed_block_types_all'])->not->toBeEmpty()
                ->and(array_keys(BlockTypeFilter::getRestrictions()))->toBe(['one', 'two']);
        } finally {
            setStatic(BlockTypeFilter::class, 'restrictions', $restrictions);
            setStatic(BlockTypeFilter::class, 'filterAdded', $added);
        }
    });

    test('the filter is set up by registering restrictions even when it was not added yet', function () {
        $restrictions = setStatic(BlockTypeFilter::class, 'restrictions', []);
        $added = setStatic(BlockTypeFilter::class, 'filterAdded', false);

        try {
            BlockTypeFilter::registerRestrictions('three', ['excluded' => ['page']]);

            expect((new ReflectionProperty(BlockTypeFilter::class, 'filterAdded'))->getValue())->toBeTrue();
        } finally {
            setStatic(BlockTypeFilter::class, 'restrictions', $restrictions);
            setStatic(BlockTypeFilter::class, 'filterAdded', $added);
        }
    });
});

describe('models', function () {
    test('every label is kept', function () {
        $model = MetaboxModel::make('labelled')->table('labelled')->labels(
            'Items', 'Item', 'Menu', 'All', 'Add', 'Add item', 'Edit', 'Search', 'None', 'Added', 'Updated', 'Deleted',
        );

        expect($model->build()['labels'])->toBe([
            'name' => 'Items',
            'singular_name' => 'Item',
            'menu_name' => 'Menu',
            'all_items' => 'All',
            'add_new' => 'Add',
            'add_new_item' => 'Add item',
            'edit_item' => 'Edit',
            'search_items' => 'Search',
            'not_found' => 'None',
            'item_added' => 'Added',
            'item_updated' => 'Updated',
            'item_deleted' => 'Deleted',
        ]);
    });

    test('the supports are a list without duplicates', function () {
        $supports = MetaboxModel::make('supporting')->table('supporting')
            ->supports(ModelSupport::Author, ModelSupport::Author, 'published_date')
            ->build()['supports'];

        expect($supports)->toBe(['author', 'published_date']);
    });

    test('a table named like a class without getTable() is a plain table', function () {
        expect(MetaboxModel::make('plain_class')->table('stdClass')->getTable())->toBe('wp_stdClass');
    });
});

describe('relationships', function () {
    test('a reciprocal relationship connects the same taxonomies', function () {
        expect(messageOf(fn () => Relationship::make('tags')->from(Side::terms('one'))->to(Side::terms('two'))->reciprocal()->build()))
            ->toBe("The reciprocal relationship 'tags' must connect objects of the same type.")
            ->and(Relationship::make('tags_ok')->from(Side::terms('one'))->to(Side::terms('one'))->reciprocal()->build())
            ->toHaveKey('reciprocal', true);
    });

    test('a side meta box keeps all its settings', function () {
        $side = Side::posts()->metaBox('Title', Context::Side, 'low', true, BoxStyle::Seamless, 'my-class', true);

        expect($side->build(Side::posts('page')))->toMatchArray(['meta_box' => [
            'title' => 'Title',
            'context' => 'side',
            'priority' => 'low',
            'closed' => true,
            'style' => 'seamless',
            'class' => 'my-class',
            'hidden' => true,
        ]]);
    });

    test('a side field accepts a maximum of 1 and refuses less', function () {
        expect(Side::posts()->field(max: 1)->build(Side::posts('page')))->not->toHaveKey('field')
            ->and(Side::posts('page')->build(Side::posts()->field(max: 1)))->toMatchArray(['field' => ['max_clone' => 1]])
            ->and(messageOf(fn () => Side::posts()->field(max: 0)))
            ->toBe('The maximum number of connected objects must be at least 1, 0 given.');
    });

    test('a side admin column can replace a column', function () {
        $other = Side::posts('page');
        $built = fn (Side $side) => $side->build($other);

        expect($built(Side::posts()->adminColumn(replace: 'title', title: 'T', link: AdminColumnLink::Edit))['admin_column'])
            ->toBe(['position' => 'replace title', 'title' => 'T', 'link' => 'edit'])
            ->and($built(Side::posts()->adminColumn(link: false))['admin_column'])->toBe(['link' => false])
            ->and(messageOf(fn () => Side::posts()->adminColumn(before: 'a', replace: 'b')))
            ->toBe('An admin column can only be placed before, after or instead of one column.');
    });
});

describe('rules', function () {
    test('category and is_child rules work with show and hide too', function () {
        expect(Rule::all(Rule::category(1))->toArray('show'))->toBe(['relation' => 'AND', 'category' => [1]])
            ->and(Rule::all(Rule::category(1))->toArray('include'))->toBe(['relation' => 'AND', 'category' => [1]])
            ->and(Rule::all(Rule::isChild())->toArray('hide'))->toBe(['relation' => 'AND', 'is_child' => true])
            ->and(Rule::all(Rule::isChild(false))->toArray('exclude'))->toBe(['relation' => 'AND', 'is_child' => false]);
    });
});

describe('field settings', function () {
    test('a field admin column keeps all its settings', function () {
        expect(Number::make('Price', 'price')->adminColumn(
            replace: 'title',
            title: 'Price',
            sortable: true,
            searchable: true,
            filterable: true,
            link: AdminColumnLink::View,
            width: '80px',
            prepend: '$',
            append: 'EUR',
        )->build()['admin_columns'])->toBe([
            'position' => 'replace title',
            'title' => 'Price',
            'before' => '$',
            'after' => 'EUR',
            'sort' => true,
            'searchable' => true,
            'filterable' => true,
            'link' => 'view',
            'width' => '80px',
        ]);
    });

    test('a field admin column message is complete', function () {
        expect(messageOf(fn () => Text::make('A', 'a')->adminColumn(before: 'x', after: 'y')))
            ->toBe('An admin column can only be placed before, after or instead of one column.')
            ->and(messageOf(fn () => Text::make('A', 'a')->adminColumn(sortable: 'alpha')))
            ->toBe("An admin column is sortable with true or 'numeric', 'alpha' given.");
    });

    test('input tooltips are supported on these types', function (string $type) {
        $call = (new ReflectionMethod(Text::class, 'ensureInputTooltipSupported'))->invoke(null, $type);

        expect($call)->toBeNull();
    })->with(['date', 'datetime', 'email', 'number', 'password', 'text', 'time', 'url', 'select', 'select_advanced']);

    test('input tooltips are refused on other types', function () {
        expect(messageOf(fn () => (new ReflectionMethod(Text::class, 'ensureInputTooltipSupported'))->invoke(null, 'checkbox')))
            ->toBe('Meta Box Tooltip does not support input tooltips on checkbox fields.');
    });

    test('a slider is a minimum range by default', function () {
        expect(Slider::make('S', 's')->build()['js_options'])->toBe(['range' => 'min'])
            ->and(Slider::make('S', 's')->range()->build()['js_options'])->toBe(['range' => true])
            ->and(Slider::make('S', 's')->range(false)->build()['js_options'])->toBe(['range' => 'min']);
    });

    test('a range step must be numeric, any, or a numeric string', function () {
        expect(Range::make('R', 'r')->step('any')->build())->toHaveKey('step', 'any')
            ->and(Range::make('R', 'r')->step('0.5')->build())->toHaveKey('step', '0.5')
            ->and(messageOf(fn () => Range::make('R', 'r')->step('abc')))
            ->toBe("Step must be a numeric value, 'any', or null.");
    });

    test('a tree keeps integer keys as string values', function () {
        $options = SelectTree::make('Tree', 'tree')->tree([5 => 'Five', 6 => ['label' => 'Six', 'children' => [7 => 'Seven']]])->build()['options'];

        expect($options)->toBe([
            ['value' => '5', 'label' => 'Five'],
            ['value' => '6', 'label' => 'Six'],
            ['value' => '7', 'label' => 'Seven', 'parent' => '6'],
        ]);
    });
});

describe('settings pages', function () {
    test('the icon svg only applies to the svg icon type', function () {
        expect(SettingsPage::make('P', 'p')->iconType(IconType::DASHICONS)->icon('admin-generic')->iconSvg('<svg></svg>')->build())
            ->toHaveKey('icon_url', 'dashicons-admin-generic')
            ->and(SettingsPage::make('Q', 'q')->iconType(IconType::SVG)->icon('custom-class')->build())
            ->toHaveKey('icon_url', 'custom-class');
    });
});

describe('conditional logic', function () {
    test('two arguments compare for equality and three give the operator', function (string $method, string $setting) {
        expect(Text::make('A', 'a')->{$method}('type', 'custom')->build()[$setting])->toBe(['type', '=', 'custom'])
            ->and(Text::make('A', 'a')->{$method}('price', '>', 100)->build()[$setting])->toBe(['price', '>', 100]);
    })->with([
        'visibleWhen' => ['visibleWhen', 'visible'],
        'orVisibleWhen' => ['orVisibleWhen', 'visible'],
        'hiddenWhen' => ['hiddenWhen', 'hidden'],
        'orHiddenWhen' => ['orHiddenWhen', 'hidden'],
    ]);

    test('the conditions of the same relation are chained', function () {
        $and = Text::make('A', 'a')->visibleWhen('x', 1)->visibleWhen('y', 2)->visibleWhen('z', 3)->build()['visible'];
        $or = Text::make('A', 'a')->orHiddenWhen('x', 1)->orHiddenWhen('y', 2)->orHiddenWhen('z', 3)->build()['hidden'];

        expect($and)->toBe([['x', '=', 1], ['y', '=', 2], ['z', '=', 3]])
            ->and($or)->toBe(['when' => [['x', '=', 1], ['y', '=', 2], ['z', '=', 3]], 'relation' => 'or']);
    });

    test('the first condition can be followed by the other relation', function () {
        expect(Text::make('A', 'a')->visibleWhen('x', 1)->orVisibleWhen('y', 2)->build()['visible'])
            ->toBe(['when' => [['x', '=', 1], ['y', '=', 2]], 'relation' => 'or'])
            ->and(Text::make('A', 'a')->orVisibleWhen('x', 1)->visibleWhen('y', 2)->build()['visible'])
            ->toBe([['x', '=', 1], ['y', '=', 2]]);
    });

    test('AND and OR conditions cannot be mixed once there are two', function () {
        $message = 'Meta Box conditional logic cannot mix AND and OR conditions on the same element.';

        expect(messageOf(fn () => Text::make('A', 'a')->orVisibleWhen('x', 1)->orVisibleWhen('y', 2)->visibleWhen('z', 3)))->toBe($message)
            ->and(messageOf(fn () => Text::make('A', 'a')->visibleWhen('x', 1)->visibleWhen('y', 2)->orVisibleWhen('z', 3)))->toBe($message);
    });
});
