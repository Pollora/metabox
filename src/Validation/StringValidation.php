<?php

declare(strict_types=1);

namespace Pollora\Metabox\Validation;

use InvalidArgumentException;

/**
 * Ensures that the provided value is a string.
 *
 * @param  mixed  $value  The value to be validated.
 * @param  string  $enumClass  The fully-qualified class name of the enum class.
 *
 * @throws InvalidArgumentException If the value is not a string.
 */
final class StringValidation
{
    /**
     * Ensures that the provided value is a string.
     *
     * @param  mixed  $value  The value to be validated.
     * @param  string  $enumClass  The fully-qualified class name of the enum class.
     *
     * @throws InvalidArgumentException If the value is not a string.
     *
     * @phpstan-assert string $value
     */
    public static function ensureIsString(mixed $value, string $enumClass): void
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("Type must be either a string or an instance of {$enumClass}.");
        }
    }
}
