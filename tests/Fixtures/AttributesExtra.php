<?php

declare(strict_types=1);

namespace Pollora\Metabox\AttributesExtra;

use Attribute;
use Pollora\Metabox\Attributes\FieldGroup;

/**
 * A foreign attribute whose namespace only starts like the package's one.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Marker {}

#[FieldGroup('Foreign', 'foreign')]
#[Marker]
final class WithMarker
{
    public function fields(): array
    {
        return [];
    }
}
