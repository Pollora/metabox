<?php

declare(strict_types=1);

use Pollora\Metabox\Enums\BoxStyle;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Color;
use Pollora\Metabox\Fields\Tab;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Validation\OptionValidation;

it('takes a null passed to cloneable() as not cloneable', function (): void {
    expect(Text::make('Links', 'links')->cloneable(null)->build()['clone'])->toBeFalse();
});

it('reports an unknown option with the allowed values when asked for no message of its own', function (): void {
    expect(fn (): string => OptionValidation::check('nope', BoxStyle::class, true))
        ->toThrow(InvalidArgumentException::class, 'seamless');
});

it('accepts the boolean palettes of the WordPress colour picker', function (bool $palettes): void {
    set_error_handler(static function (int $level, string $message): never {
        throw new ErrorException($message, 0, $level);
    });

    try {
        $field = Color::make('Accent', 'accent')->jsOptions(['palettes' => $palettes])->build();
    } finally {
        restore_error_handler();
    }

    expect($field['js_options'])->toBe(['palettes' => $palettes]);
})->with([true, false]);

it('adds a tab without any field', function (): void {
    set_error_handler(static function (int $level, string $message): never {
        throw new ErrorException($message, 0, $level);
    });

    try {
        $group = FieldGroup::make('Group', 'group')->fields([Tab::make('Empty', 'empty')])->build();
    } finally {
        restore_error_handler();
    }

    expect($group['tabs'])->toBe(['empty' => ['label' => 'Empty']])
        ->and($group)->not->toHaveKey('fields');
});
