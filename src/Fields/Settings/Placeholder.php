<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

/**
 * Trait for setting a placeholder for a field.
 */
trait Placeholder
{
    /**
     * The placeholder text for the field.
     *
     * @var string|array<mixed>
     */
    protected string|array $placeholder;

    /**
     * Set the placeholder text for the field.
     *
     * @param  string|array<mixed>  $placeholder  The placeholder text. Array with 'key' and 'value' as keys and placeholder text as values for the KeyValye field.
     * @return static The instance of the class with the updated placeholder.
     */
    public function placeholder(string|array $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }
}
