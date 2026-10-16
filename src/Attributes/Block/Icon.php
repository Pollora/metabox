<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\Block;

use Attribute;
use Pollora\Metabox\Attributes\Contracts\BlockSetting;
use Pollora\Metabox\Block;

/**
 * Sets the block icon: a Dashicons name without the 'dashicons-' prefix, or an SVG.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Icon implements BlockSetting
{
    public function __construct(public string $icon) {}

    public function configure(Block $block): void
    {
        $block->icon($this->icon);
    }
}
