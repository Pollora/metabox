<?php

declare(strict_types=1);

use Pollora\Metabox\CustomTable\TableName;
use Pollora\Metabox\Enums\BoxStyle;
use Pollora\Metabox\Enums\Priority;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Location;
use Pollora\Metabox\Services\BlockTypeFilter;
use Pollora\Metabox\SettingsPage;
use Pollora\Metabox\Transformer\EnumTransformer;
use Pollora\Metabox\Validation\OptionValidation;

it('reports a model whose getTable() gives no table name', function (): void {
    $model = new class
    {
        public function getTable(): ?string
        {
            return null;
        }
    };

    expect(fn (): string => (new TableName($model::class))->resolve())
        ->toThrow(LogicException::class, 'must return a table name');
});

it('rejects a class that is not a backed enum', function (): void {
    expect(fn (): string => EnumTransformer::convertStringToEnumValue('a', stdClass::class))
        ->toThrow(InvalidArgumentException::class, 'Invalid enum class');
});

it('adds a declaration to the list a filter holds, and starts one when it holds something else', function (string $filter, Closure $declare): void {
    $GLOBALS['test_filters'] = [];
    $declare();

    expect(apply_filters($filter, [['id' => 'first']]))->toHaveCount(2)
        ->and(apply_filters($filter, null))->toHaveCount(1);
})->with([
    'meta boxes' => ['rwmb_meta_boxes', fn (): FieldGroup => FieldGroup::make('Group', 'group')],
    'settings pages' => ['mb_settings_pages', fn (): SettingsPage => SettingsPage::make('Page', 'page')],
]);

it('keeps the error message it is given', function (): void {
    expect(fn (): string => EnumTransformer::convertStringToEnumValue('nope', BoxStyle::class, 'Pick a style.'))
        ->toThrow(InvalidArgumentException::class, 'Pick a style.');
});

it('refuses the case of another enum', function (): void {
    expect(fn (): string => OptionValidation::check(Priority::High, BoxStyle::class))
        ->toThrow(InvalidArgumentException::class, 'Type must be either a string or an instance of');
});

it('names a model given as a number when it is not declared', function (): void {
    $group = FieldGroup::make('Group', 'group')->location(Location::where('models', [5]));

    expect(fn (): array => $group->build())->toThrow(LogicException::class, "The model '5' is not declared");
});

it('adds no prefix when WordPress gives no database object', function (mixed $wpdb): void {
    $previous = $GLOBALS['wpdb'] ?? null;
    $GLOBALS['wpdb'] = $wpdb;

    try {
        expect((new TableName('items'))->resolve())->toBe('items');
    } finally {
        $GLOBALS['wpdb'] = $previous;
    }
})->with(['none' => [null], 'without a prefix' => [new stdClass]]);

it('reads a single post type given without a list in a block restriction', function (): void {
    $editor = (object) ['post' => (object) ['post_type' => 'page']];
    BlockTypeFilter::registerRestrictions('single-allowed', ['allowed' => 'post']);
    BlockTypeFilter::registerRestrictions('single-excluded', ['excluded' => 'page']);

    expect(BlockTypeFilter::filterBlockTypes(['meta-box/single-allowed', 'meta-box/single-excluded', 'core/paragraph'], $editor))
        ->toBe(['core/paragraph']);
});
