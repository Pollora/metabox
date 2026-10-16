<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\Block;

use Attribute;
use Pollora\Metabox\Attributes\Contracts\BlockSetting;
use Pollora\Metabox\Block;
use Pollora\Metabox\Enums\BlockMode;

/**
 * Sets the default mode of the block: edit fields or rendered preview.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Mode implements BlockSetting
{
    public function __construct(public string|BlockMode $mode) {}

    public function configure(Block $block): void
    {
        $block->mode($this->mode);
    }
}
