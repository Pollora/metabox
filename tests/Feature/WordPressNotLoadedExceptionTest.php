<?php

declare(strict_types=1);

use Pollora\Metabox\Exception\WordPressNotLoadedException;

it('keeps the message and stays an Exception, as the plain Exception it replaces', function (): void {
    $exception = WordPressNotLoadedException::forMetaboxMaker();

    expect($exception)->toBeInstanceOf(Exception::class)
        ->and($exception->getMessage())->toBe('Metabox Maker requires WordPress to be loaded.');
});
