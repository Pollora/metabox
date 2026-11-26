<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

use Pollora\Metabox\Validation\ColorValidation;

/**
 * Trait to add js options to fields.
 */
trait JsOptions
{
    /**
     * JavaScript options for the field.
     *
     * @var array<mixed>
     */
    protected array $js_options;

    /**
     * Set JavaScript options for the field, merged with the options set before.
     *
     * @param  array<mixed>  $js_options  JavaScript options.
     * @return static Returns the instance of the field class for method chaining.
     */
    public function jsOptions(array $js_options): static
    {
        // The WordPress colour picker also takes a boolean here: only a list of colours is validated.
        if (is_array($js_options['palettes'] ?? null)) {
            foreach ($js_options['palettes'] as $color) {
                ColorValidation::ensureIsColor(is_string($color) ? $color : get_debug_type($color));
            }
        }

        $this->js_options = array_merge($this->js_options ?? [], $js_options);

        return $this;
    }
}
