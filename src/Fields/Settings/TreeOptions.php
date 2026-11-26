<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

use InvalidArgumentException;

/**
 * Trait for hierarchical options, displayed as a tree.
 */
trait TreeOptions
{
    /**
     * The options, each with a value, a label and the value of its parent.
     *
     * @var array<mixed>|string
     */
    protected array|string $options;

    /**
     * Whether the options are displayed as a flat list instead of a tree.
     */
    protected bool $flatten;

    /**
     * Set hierarchical options, displayed as a tree: the children of an option are
     * shown when it is selected.
     *
     * Each option is a 'value' => 'Label' pair, or 'value' => ['label' => 'Label',
     * 'children' => [...]] for an option with children.
     *
     * @param  array<mixed>  $tree
     */
    public function tree(array $tree): static
    {
        $this->options = [];
        $this->flatten = false;
        $this->addTreeOptions($tree, null);

        return $this;
    }

    /**
     * Add the options of a tree level, in the Meta Box format.
     *
     * @param  array<mixed>  $tree
     */
    private function addTreeOptions(array $tree, ?string $parent): void
    {
        foreach ($tree as $value => $option) {
            $value = (string) $value;

            if (in_array($value, array_column($this->options, 'value'), true)) {
                throw new InvalidArgumentException("The option '{$value}' is defined twice in the tree.");
            }

            if (is_array($option) && (! is_string($option['label'] ?? null) || array_diff(array_keys($option), ['label', 'children']) !== [] || ! is_array($option['children'] ?? []))) {
                throw new InvalidArgumentException("The option '{$value}' must be a label, or an array with a 'label' and its 'children'.");
            }

            if (! is_array($option) && ! is_string($option)) {
                throw new InvalidArgumentException("The option '{$value}' must be a label, or an array with a 'label' and its 'children'.");
            }

            $this->options[] = array_filter([
                'value' => $value,
                'label' => is_array($option) ? $option['label'] : $option,
                'parent' => $parent,
            ], fn ($item) => $item !== null);

            if (is_array($option)) {
                $this->addTreeOptions($option['children'] ?? [], $value);
            }
        }
    }
}
