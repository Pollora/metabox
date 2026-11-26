<?php

declare(strict_types=1);

namespace Pollora\Metabox\Transformer;

use InvalidArgumentException;
use Pollora\Metabox\Exception\Enum;

final class EnumTransformer
{
    /**
     * Convert a string to an enum value.
     *
     * @param  string  $value  The string to be converted to an enum value.
     * @param  string  $enumClass  The fully qualified class name of the enum.
     * @param  string|bool  $errorMessage  An optional error message to be thrown if the string does not match any enum value.
     * @return string The enum value corresponding to the input string.
     *
     * @throws InvalidArgumentException If the input string does not match any enum value and an error message is not provided.
     */
    public static function convertStringToEnumValue(string $value, string $enumClass, string|bool|null $errorMessage = null): string
    {
        $enumValue = $enumClass::tryFrom($value);
        if ($enumValue === null) {
            if (! is_string($errorMessage) || ! $errorMessage) {
                $errorMessage = Enum::generateErrorMessageFromEnumValues($enumClass);
            }
            throw new InvalidArgumentException($errorMessage);
        }

        return $enumValue->value;
    }
}
