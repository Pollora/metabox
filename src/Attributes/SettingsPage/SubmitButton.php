<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\SettingsPage;

use Attribute;
use Pollora\Metabox\Attributes\Contracts\SettingsPageSetting;
use Pollora\Metabox\SettingsPage;

/**
 * Sets the text of the submit button.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SubmitButton implements SettingsPageSetting
{
    public function __construct(public string $submitButton) {}

    public function configure(SettingsPage $page): void
    {
        $page->submitButton($this->submitButton);
    }
}
