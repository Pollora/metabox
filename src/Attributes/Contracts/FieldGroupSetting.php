<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\Contracts;

use Pollora\Metabox\FieldGroup;

/**
 * An attribute applying a setting to a declared field group or block.
 */
interface FieldGroupSetting
{
    public function configure(FieldGroup $group): void;
}
