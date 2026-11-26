<?php

declare(strict_types=1);

namespace Pollora\Metabox\Validation;

use BackedEnum;
use InvalidArgumentException;
use Pollora\Metabox\Transformer\EnumTransformer;

/**
 * Helper class for validating and converting values to enums.
 */
final class OptionValidation
{
    /**
     * Validates and converts a string to an enum or returns the enum directly.
     * Throws an exception if the input is neither a valid string nor an enum instance.
     *
     * @param  mixed  $value  The value to convert or verify.
     * @param  string  $enumClass  The enum class to check against.
     * @param  string|bool  $errorMessage  The error message for the exception if validation fails.
     * @return string The validated enum instance.
     *
     * @throws InvalidArgumentException If the input is neither a valid string nor an enum instance.
     */
    public static function check(mixed $value, string $enumClass, string|bool $errorMessage = false): string
    {
        EnumValidation::validateEnumClass($enumClass);

        if ($value instanceof $enumClass && $value instanceof BackedEnum && is_string($value->value)) {
            return $value->value;
        }

        StringValidation::ensureIsString($value, $enumClass);

        return EnumTransformer::convertStringToEnumValue($value, $enumClass, $errorMessage);
    }
}
