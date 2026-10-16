<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes;

use Attribute;

/**
 * Declares a settings page (MB Settings Page). Field groups are displayed on it with #[SettingsPages].
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SettingsPage
{
    public function __construct(
        public string $title,
        public string $id,
    ) {}
}
