<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Utils;

use Pollora\Metabox\Transformer\EmptyValueFilter;

/**
 * Trait to build fields.
 */
trait Builder
{
    /**
     * Builds the field and returns its properties as an associative array.
     *
     * @return array<mixed> The properties of the field.
     */
    public function build(): array
    {
        $fieldData = EmptyValueFilter::filter(get_object_vars($this));

        // Remove the settings array from the field data
        unset($fieldData['settings']);

        // Merge custom settings if they exist
        if (method_exists($this, 'getSettings') && ! empty($settings = $this->getSettings())) {
            $fieldData = array_merge($fieldData, (array) $settings);
        }

        return $fieldData;
    }
}
