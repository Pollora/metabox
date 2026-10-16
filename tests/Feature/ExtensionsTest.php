<?php

declare(strict_types=1);

use Pollora\Metabox\Enums\AdminColumnLink;
use Pollora\Metabox\Enums\ToggleType;
use Pollora\Metabox\Enums\TooltipPosition;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Checkbox;
use Pollora\Metabox\Fields\Divider;
use Pollora\Metabox\Fields\Email;
use Pollora\Metabox\Fields\Heading;
use Pollora\Metabox\Fields\Number;
use Pollora\Metabox\Fields\Select;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Fields\Textarea;
use Pollora\Metabox\Fields\Wysiwyg;

describe('conditional logic', function () {
    test('two arguments compare with =', function () {
        expect(Text::make('URL', 'custom_url')->visibleWhen('link_type', 'custom')->build())
            ->toHaveKey('visible', ['link_type', '=', 'custom']);
    });

    test('produces what clients wrote with setting()', function () {
        expect(Text::make('Label', 'custom_label')->visibleWhen('link_type', '=', 'page')->build()['visible'])
            ->toBe(['link_type', '=', 'page'])
            ->and(Text::make('Label', 'label')->hiddenWhen('isToggle', '1')->build()['hidden'])
            ->toBe(['isToggle', '=', '1']);
    });

    test('three arguments use the operator', function () {
        expect(Number::make('Discount', 'discount')->visibleWhen('price', '>', 100)->build())
            ->toHaveKey('visible', ['price', '>', 100]);
    });

    test('accepts the field instance', function () {
        $linkType = Select::make('Link type', 'link_type')->options(['page' => 'Page', 'custom' => 'Custom']);

        expect(Text::make('URL', 'custom_url')->visibleWhen($linkType, 'custom')->build())
            ->toHaveKey('visible', ['link_type', '=', 'custom']);
    });

    test('successive conditions are combined with AND', function () {
        expect(Text::make('Model', 'model')
            ->visibleWhen('brand', 'Apple')
            ->visibleWhen('year', 'between', [2010, 2015])
            ->build())->toHaveKey('visible', [
                ['brand', '=', 'Apple'],
                ['year', 'between', [2010, 2015]],
            ]);
    });

    test('or methods combine conditions with OR', function () {
        expect(Text::make('Model', 'model')
            ->hiddenWhen('brand', 'Apple')
            ->orHiddenWhen('brand', 'Samsung')
            ->orHiddenWhen('year', '<', 2000)
            ->build())->toHaveKey('hidden', [
                'when' => [
                    ['brand', '=', 'Apple'],
                    ['brand', '=', 'Samsung'],
                    ['year', '<', 2000],
                ],
                'relation' => 'or',
            ]);
    });

    test('a checkbox condition keeps its boolean value', function () {
        $toggle = Checkbox::make('Toggle', 'is_toggle');

        expect(Text::make('Title', 'title')->visibleWhen($toggle, true)->build())
            ->toHaveKey('visible', ['is_toggle', '=', true]);
    });

    test('operators are case insensitive and validated', function () {
        expect(Text::make('A', 'a')->visibleWhen('b', 'NOT IN', [1, 2])->build())
            ->toHaveKey('visible', ['b', 'not in', [1, 2]]);

        Text::make('A', 'a')->visibleWhen('b', '=>', 1);
    })->throws(InvalidArgumentException::class, "Invalid conditional logic operator '=>'");

    test('cannot mix AND and OR', function () {
        Text::make('A', 'a')
            ->visibleWhen('b', 1)
            ->visibleWhen('c', 2)
            ->orVisibleWhen('d', 3);
    })->throws(LogicException::class);

    test('works on layout fields and meta boxes', function () {
        expect(Heading::make('Section')->visibleWhen('type', 'custom')->build())
            ->toHaveKey('visible', ['type', '=', 'custom'])
            ->and(Divider::make()->hiddenWhen('type', 'custom')->build())
            ->toHaveKey('hidden', ['type', '=', 'custom'])
            ->and(FieldGroup::make('Box', 'box')->hiddenWhen('post_format', 'aside')->toggleType(ToggleType::Fade)->build())
            ->toMatchArray(['hidden' => ['post_format', '=', 'aside'], 'toggle_type' => 'fade']);
    });
});

describe('columns', function () {
    test('sets the grid span', function () {
        expect(Text::make('First name', 'first_name')->columns(6)->build())->toHaveKey('columns', 6);
    });

    test('rejects spans outside 1 to 12', function () {
        Text::make('First name', 'first_name')->columns(13);
    })->throws(InvalidArgumentException::class);
});

describe('tooltip', function () {
    test('content only', function () {
        expect(Text::make('Price', 'price')->tooltip('Price including VAT')->build())
            ->toHaveKey('tooltip', 'Price including VAT');
    });

    test('with options', function () {
        expect(Text::make('Price', 'price')->tooltip('<b>VAT</b> included', icon: 'help', position: TooltipPosition::Right, allowHtml: true)->build())
            ->toHaveKey('tooltip', ['content' => '<b>VAT</b> included', 'icon' => 'help', 'position' => 'right', 'allow_html' => true]);
    });
});

describe('admin columns', function () {
    test('default column', function () {
        expect(Text::make('Price', 'price')->adminColumn()->build())->toHaveKey('admin_columns', true);
    });

    test('with options', function () {
        expect(Number::make('Price', 'price')->adminColumn(
            after: 'title',
            title: 'Price',
            sortable: 'numeric',
            searchable: true,
            link: AdminColumnLink::Edit,
            prepend: '$',
        )->build())->toHaveKey('admin_columns', [
            'position' => 'after title',
            'title' => 'Price',
            'before' => '$',
            'sort' => 'numeric',
            'searchable' => true,
            'link' => 'edit',
        ]);
    });

    test('only one position', function () {
        Text::make('Price', 'price')->adminColumn(before: 'title', after: 'date');
    })->throws(InvalidArgumentException::class);
});

describe('text limiter', function () {
    test('characters and words', function () {
        expect(Textarea::make('Excerpt', 'excerpt')->maxCharacters(160)->build())
            ->toMatchArray(['limit' => 160, 'limit_type' => 'character'])
            ->and(Wysiwyg::make('Intro', 'intro')->maxWords(40)->build())
            ->toMatchArray(['limit' => 40, 'limit_type' => 'word'])
            ->and(Text::make('Title', 'title')->maxCharacters(60)->build())
            ->toMatchArray(['limit' => 60, 'limit_type' => 'character']);
    });

    test('is only available on the field types MB Text Limiter supports', function () {
        expect(method_exists(Email::class, 'maxCharacters'))->toBeFalse();

        Text::make('Email', 'email')->type('email')->maxCharacters(60);
    })->throws(LogicException::class);
});
