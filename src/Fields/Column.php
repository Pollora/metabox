<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields;

use InvalidArgumentException;
use Pollora\Metabox\Contract\Renderable;

/**
 * A column of the 12-column grid holding several fields (Meta Box Columns).
 *
 * @phpstan-consistent-constructor
 */
class Column
{
    /**
     * The fields of the column.
     *
     * @var array<Renderable>
     */
    protected array $fields = [];

    /**
     * The CSS class of the column.
     */
    protected string $class;

    /**
     * @param  int  $size  The number of grid columns the column spans, from 1 to 12.
     */
    public function __construct(protected int $size)
    {
        if ($size < 1 || $size > 12) {
            throw new InvalidArgumentException("A column spans 1 to 12 grid columns, {$size} given.");
        }
    }

    /**
     * Create a column spanning the given number of grid columns, from 1 to 12: 6 is half the width.
     */
    public static function make(int $size): static
    {
        return new static($size);
    }

    /**
     * Add a CSS class to the column.
     */
    public function class(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Add fields to the column, displayed one below the other.
     *
     * @param  array<mixed>  $fields  Fields, headings and dividers.
     */
    public function fields(array $fields): static
    {
        foreach ($fields as $field) {
            if ($field instanceof Tab || ! $field instanceof Renderable) {
                throw new InvalidArgumentException('A column only holds fields, headings and dividers.');
            }

            $this->fields[] = $field;
        }

        return $this;
    }

    /**
     * @return array<Renderable>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * The column settings for the meta box.
     *
     * @return int|array<mixed>
     */
    public function build(): int|array
    {
        return isset($this->class) ? ['size' => $this->size, 'class' => $this->class] : $this->size;
    }
}
