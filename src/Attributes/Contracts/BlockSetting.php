<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\Contracts;

use Pollora\Metabox\Block;

/**
 * An attribute applying a setting to a declared block.
 */
interface BlockSetting
{
    public function configure(Block $block): void;
}
