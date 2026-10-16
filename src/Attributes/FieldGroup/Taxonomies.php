<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\FieldGroup;

use Attribute;
use Pollora\Metabox\Attributes\Target;

/**
 * Displays the field group on terms of these taxonomies (MB Term Meta): slugs, or classes declared with Pollora's #[Taxonomy].
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Taxonomies extends Target
{
    public function key(): string
    {
        return 'taxonomies';
    }
}
