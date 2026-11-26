<?php

declare(strict_types=1);

namespace Pollora\Metabox\Exception;

use BackedEnum;

/**
 * EnumException class provides a static method to generate an error message based on the allowed values of an enum class.
 */
final class Enum
{
    /**
     * Generate an error message from enum values.
     *
     * This method takes a string parameter representing the enum class and returns an error message with the allowed values.
     *
     * @param  string  $enumClass  The enum class to get the allowed values from.
     * @return string The error message with the allowed values.
     */
    public static function generateErrorMessageFromEnumValues(string $enumClass): string
    {
        $values = array_map(
            static fn (BackedEnum $case): int|string => $case->value,
            is_subclass_of($enumClass, BackedEnum::class) ? $enumClass::cases() : []
        );
        $formattedValues = implode("', '", $values);

        return "Invalid value specified. Allowed values are '{$formattedValues}'.";
    }
}
