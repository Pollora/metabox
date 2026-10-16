<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\Block;

use Attribute;
use Pollora\Metabox\Attributes\Contracts\BlockSetting;
use Pollora\Metabox\Block;

/**
 * Sets the block description, shown in the block inserter and sidebar.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Description implements BlockSetting
{
    public function __construct(public string $description) {}

    public function configure(Block $block): void
    {
        $block->description($this->description);
    }
}
