<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\SettingsPage;

use Attribute;
use Pollora\Metabox\Attributes\Contracts\SettingsPageSetting;
use Pollora\Metabox\SettingsPage;

/**
 * Sets the menu icon: a Dashicons name, e.g. 'admin-generic'.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Icon implements SettingsPageSetting
{
    public function __construct(public string $icon) {}

    public function configure(SettingsPage $page): void
    {
        $page->icon($this->icon);
    }
}
