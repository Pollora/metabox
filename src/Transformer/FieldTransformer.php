<?php

declare(strict_types=1);

namespace Pollora\Metabox\Transformer;

use Pollora\Metabox\Contract\Renderable;
use Pollora\Metabox\Fields\Column;
use Pollora\Metabox\Fields\Tab;

/**
 * Trait for handling field transformations.
 */
trait FieldTransformer
{
    /**
     * Adds fields to the transformer.
     *
     * @param  array<mixed>  $fields  An array of fields to add.
     * @return static The transformed instance.
     */
    public function buildFieldset(array $fields): static
    {
        foreach ($fields as $field) {
            if ($field instanceof Tab) {
                $this->processTab($field);
            } elseif ($field instanceof Column) {
                $this->addColumn($field);
            } elseif ($field instanceof Renderable) {
                $this->addField($field);
            }
        }

        return $this;
    }

    /**
     * Process a Tab object and its fields.
     *
     * @param  Tab  $tab  The Tab object.
     */
    protected function processTab(Tab $tab): void
    {
        $tabData = $tab->build();
        $this->tabs[$tabData['id']] = $this->filterTabData($tabData);

        // A tab without fields has no 'fields' key: empty values are left out of what it builds.
        foreach ($tabData['fields'] ?? [] as $subField) {
            if ($subField instanceof Column) {
                $this->addColumn($subField, $tabData['id']);
            } elseif ($subField instanceof Renderable) {
                $this->addField($subField);
            }
        }
    }

    /**
     * Add a single Field to the fields array.
     *
     * @param  Renderable  $field  The field to add.
     */
    protected function addField(Renderable $field): void
    {
        $this->fields[] = $field->build();
    }

    /**
     * Add the fields of a column, and the column to the meta box columns (Meta Box Columns).
     *
     * @param  string|null  $tab  The ID of the tab holding the column.
     */
    protected function addColumn(Column $column, ?string $tab = null): void
    {
        $id = 'column-'.(count($this->columns) + 1);
        $this->columns[$id] = $column->build();

        foreach ($column->getFields() as $field) {
            $this->fields[] = array_filter(['tab' => $tab]) + $field->build() + ['column' => $id];
        }
    }

    /**
     * Filter and format tab data.
     *
     * @param  array<mixed>  $tabData  Data from the Tab object.
     * @return array<mixed> Filtered and formatted tab data.
     */
    protected function filterTabData(array $tabData): array
    {
        return EmptyValueFilter::filter([
            'label' => $tabData['name'],
            'icon' => $tabData['icon'] ?? null,
        ]);
    }
}
