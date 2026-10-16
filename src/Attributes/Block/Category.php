<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\Block;

use Attribute;
use Pollora\Metabox\Attributes\Contracts\BlockSetting;
use Pollora\Metabox\Block;

/**
 * Sets the block category, e.g. 'design' or a category registered by the theme.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Category implements BlockSetting
{
    public function __construct(public string $category) {}

    public function configure(Block $block): void
    {
        $block->category($this->category);
    }
}
