<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields;

use InvalidArgumentException;
use Pollora\Metabox\Contract\Renderable;

/**
 * Represents a group field in a metabox.
 */
class Tab extends Field
{
    /**
     * The type of field.
     */
    protected string $type = 'tab';

    /**
     * Container for all fields including nested groups.
     *
     * @var array<mixed>
     */
    protected array $fields = [];

    /**
     * The icon for the tab.
     */
    protected string $icon;

    /**
     * Sets the icon for the tab.
     *
     * @param  string  $icon  The icon to be set.
     * @return self The instance of the Tab class for method chaining.
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Adds fields to the group, which can include nested groups.
     *
     * @param  array<mixed>  $fields  An array of Field instances to add to the group.
     * @return self The instance of the Group class for method chaining.
     *
     * @throws InvalidArgumentException If any of the fields is not an instance of Field.
     */
    public function fields(array $fields): self
    {
        foreach ($fields as $field) {
            if ($field instanceof Column) {
                $this->fields[] = $field;

                continue;
            }
            if (! $field instanceof Renderable) {
                throw new InvalidArgumentException('All fields must implement Renderable.');
            }
            if (method_exists($field, 'tab')) {
                $field->tab($this->id);
            }
            $this->fields[] = $field;
        }

        return $this;
    }
}
