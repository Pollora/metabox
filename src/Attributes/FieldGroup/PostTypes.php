<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\FieldGroup;

use Attribute;
use Pollora\Metabox\Attributes\Target;

/**
 * Displays the field group on posts of these types: slugs, or classes declared with Pollora's #[PostType].
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class PostTypes extends Target
{
    public function key(): string
    {
        return 'post_types';
    }
}
