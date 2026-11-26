<?php

declare(strict_types=1);

namespace Pollora\Metabox\Transformer;

final class EmptyValueFilter
{
    /**
     * Convert a string to an enum value.
     *
     * @param  array<mixed>  $values  The values to be filtered to an enum value.
     * @return array<mixed> The filtered array.
     */
    public static function filter(array $values): array
    {
        return array_filter($values, fn ($value) => (! is_array($value) && $value !== null) || (is_array($value) && ! empty($value)));
    }
}
