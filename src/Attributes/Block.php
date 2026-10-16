<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes;

use Attribute;

/**
 * Declares a Gutenberg block (MB Blocks): the class returns its fields from a fields() method.
 * The ID is the block name in the post content, so it is never derived from the class name.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Block
{
    public function __construct(
        public string $title,
        public string $id,
    ) {}
}
