<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\Block;

use Attribute;

/**
 * Renders the block with a view, e.g. 'blocks.quote'. Requires an integration rendering views,
 * such as pollora/metabox-bridge with Blade.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class View
{
    public function __construct(public string $view) {}
}
