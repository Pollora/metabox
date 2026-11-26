<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

/**
 * Trait to handle custom HTML5 attributes for form fields.
 */
trait Attributes
{
    /**
     * Array of custom HTML5 attributes.
     *
     * @var array<mixed>
     */
    protected array $attributes;

    /**
     * Set custom HTML attributes for the field, e.g. maxlength, pattern or data-* attributes.
     *
     * @param  array<mixed>  $attributes  Attributes in 'key' => 'value' format.
     */
    public function attributes(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = is_array($value) ? json_encode($value) : $value;
        }

        return $this;
    }

    /**
     * Set a single custom HTML attribute for the field.
     */
    public function attribute(string $key, mixed $value): static
    {
        return $this->attributes([$key => $value]);
    }

    /**
     * Get all custom attributes.
     *
     * @return array<mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes ?? [];
    }
}
