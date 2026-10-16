<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes;

use Attribute;

/**
 * Declares a field group: the class returns its fields from a fields() method.
 * Without an ID, the class name in snake case is used, e.g. 'solution_fields' for SolutionFields.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class FieldGroup
{
    public function __construct(
        public string $title,
        public ?string $id = null,
    ) {}
}
