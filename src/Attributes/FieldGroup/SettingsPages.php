<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\FieldGroup;

use Attribute;
use Pollora\Metabox\Attributes\Target;

/**
 * Displays the field group on these settings pages (MB Settings Page): IDs, or classes declared with #[SettingsPage].
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class SettingsPages extends Target
{
    public function key(): string
    {
        return 'settings_pages';
    }
}
