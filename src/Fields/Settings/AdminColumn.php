<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

use InvalidArgumentException;
use Pollora\Metabox\Enums\AdminColumnLink;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * Trait for showing the field value in a column of the admin list (MB Admin Columns).
 */
trait AdminColumn
{
    /**
     * The admin column settings, or true for a default column.
     *
     * @var bool|array<mixed>
     */
    protected bool|array $admin_columns;

    /**
     * Show the field value in a column of the admin list. Without arguments, the column is added at the end.
     *
     * @param  string|null  $before  Insert the column before this column ID, e.g. 'title'.
     * @param  string|null  $after  Insert the column after this column ID.
     * @param  string|null  $replace  Replace this column ID.
     * @param  string|null  $title  The column title. Defaults to the field name.
     * @param  bool|string  $sortable  true to sort by value, 'numeric' to sort numerically.
     * @param  bool  $searchable  Whether posts can be searched by this value.
     * @param  bool  $filterable  Whether posts can be filtered by this value (taxonomy fields only).
     * @param  string|AdminColumnLink|null  $link  Link the value to the post 'edit' screen or 'view' page.
     * @param  string|null  $width  The column width, as a CSS value.
     * @param  string|null  $prepend  HTML displayed before the value.
     * @param  string|null  $append  HTML displayed after the value.
     */
    public function adminColumn(
        ?string $before = null,
        ?string $after = null,
        ?string $replace = null,
        ?string $title = null,
        bool|string $sortable = false,
        bool $searchable = false,
        bool $filterable = false,
        string|AdminColumnLink|null $link = null,
        ?string $width = null,
        ?string $prepend = null,
        ?string $append = null,
    ): static {
        $positions = array_filter(['before' => $before, 'after' => $after, 'replace' => $replace], fn ($column) => $column !== null);

        if (count($positions) > 1) {
            throw new InvalidArgumentException('An admin column can only be placed before, after or instead of one column.');
        }

        if (is_string($sortable) && $sortable !== 'numeric') {
            throw new InvalidArgumentException("An admin column is sortable with true or 'numeric', '{$sortable}' given.");
        }

        $settings = array_filter([
            'position' => $positions === [] ? null : key($positions).' '.current($positions),
            'title' => $title,
            'before' => $prepend,
            'after' => $append,
            'sort' => $sortable ?: null,
            'searchable' => $searchable ?: null,
            'filterable' => $filterable ?: null,
            'link' => $link === null ? null : OptionValidation::check($link, AdminColumnLink::class),
            'width' => $width,
        ], fn ($value) => $value !== null);

        $this->admin_columns = $settings === [] ? true : $settings;

        return $this;
    }
}
