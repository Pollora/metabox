<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes;

/**
 * Where a declared field group is displayed. Several targets display it on each of them.
 * A value can be a slug, or a class declaring the object.
 */
abstract readonly class Target
{
    /**
     * @var list<string>
     */
    public array $values;

    public function __construct(string ...$values)
    {
        $this->values = array_values($values);
    }

    /**
     * The Meta Box location key, e.g. 'post_types'.
     */
    abstract public function key(): string;
}
