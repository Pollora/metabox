<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\SettingsPage;

use Attribute;
use Pollora\Metabox\Attributes\Contracts\SettingsPageSetting;
use Pollora\Metabox\SettingsPage;

/**
 * Sets the position of the page in the admin menu.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Position implements SettingsPageSetting
{
    public function __construct(public int $position) {}

    public function configure(SettingsPage $page): void
    {
        $page->position($this->position);
    }
}
