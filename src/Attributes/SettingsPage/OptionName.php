<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\SettingsPage;

use Attribute;
use Pollora\Metabox\Attributes\Contracts\SettingsPageSetting;
use Pollora\Metabox\SettingsPage;

/**
 * Sets the option the values are stored in. Defaults to the page ID.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class OptionName implements SettingsPageSetting
{
    public function __construct(public string $optionName) {}

    public function configure(SettingsPage $page): void
    {
        $page->optionName($this->optionName);
    }
}
