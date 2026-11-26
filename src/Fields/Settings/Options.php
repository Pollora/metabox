<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

/**
 * Trait for options.
 */
trait Options
{
    /**
     * Array of 'value' => 'Label' for the inputs or a URL for AJAX.
     *
     * @var array<mixed>|string
     */
    protected array|string $options;

    /**
     * Set the options for the inputs.
     *
     * @param  array<mixed>|string  $options  Array of 'value' => 'Label' or URL for AJAX.
     * @return static Returns the instance of the class for method chaining.
     */
    public function options(array|string $options): static
    {
        $this->options = $options;

        return $this;
    }
}
