<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\Contracts;

use Pollora\Metabox\SettingsPage;

/**
 * An attribute applying a setting to a declared settings page.
 */
interface SettingsPageSetting
{
    public function configure(SettingsPage $page): void;
}
