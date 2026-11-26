<?php

declare(strict_types=1);

use Pollora\Metabox\Enums\BoxStyle;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Validation\OptionValidation;

it('takes a null passed to cloneable() as not cloneable', function (): void {
    expect(Text::make('Links', 'links')->cloneable(null)->build()['clone'])->toBeFalse();
});

it('reports an unknown option with the allowed values when asked for no message of its own', function (): void {
    expect(fn (): string => OptionValidation::check('nope', BoxStyle::class, true))
        ->toThrow(InvalidArgumentException::class, 'seamless');
});
