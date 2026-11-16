<?php

declare(strict_types=1);

namespace Pollora\Metabox;

use LogicException;
use MB_Relationships_API;
use Pollora\Metabox\Exception\WordPressNotLoadedException;
use Pollora\Metabox\Relationships\Side;

/**
 * Relationship between posts, terms or users (MB Relationships).
 *
 * @phpstan-consistent-constructor
 */
class Relationship
{
    /**
     * The IDs of the relationships declared, to reject duplicates.
     *
     * @var array<string, true>
     */
    protected static array $declared = [];

    protected ?Side $from = null;

    protected ?Side $to = null;

    protected bool $reciprocal = false;

    /**
     * @param  string  $id  The relationship ID, used to query the connected objects.
     */
    public function __construct(protected string $id)
    {
        if (! function_exists('add_action')) {
            throw WordPressNotLoadedException::forMetaboxMaker();
        }

        if (isset(static::$declared[$id])) {
            throw new LogicException("The relationship '{$id}' is already declared.");
        }

        if (did_action('mb_relationships_init') && ! doing_action('mb_relationships_init')) {
            throw new LogicException("The relationship '{$id}' is declared too late: declare it before the init action, priority 5.");
        }

        static::$declared[$id] = true;

        // While the action runs, a callback added with a higher priority still runs.
        add_action('mb_relationships_init', function () {
            MB_Relationships_API::register($this->build());
        }, doing_action('mb_relationships_init') ? PHP_INT_MAX : 10);
    }

    /**
     * Create a relationship. It is registered with MB Relationships automatically.
     *
     * @param  string  $id  The relationship ID, used to query the connected objects.
     */
    public static function make(string $id): static
    {
        return new static($id);
    }

    /**
     * Set the "from" side of the relationship.
     */
    public function from(Side $side): static
    {
        $this->from = $side;

        return $this;
    }

    /**
     * Set the "to" side of the relationship.
     */
    public function to(Side $side): static
    {
        $this->to = $side;

        return $this;
    }

    /**
     * Make the relationship reciprocal, between objects of the same type: a single meta box,
     * set on the from side, shows the connections in both directions.
     */
    public function reciprocal(bool $reciprocal = true): static
    {
        $this->reciprocal = $reciprocal;

        return $this;
    }

    /**
     * Build the relationship and return its MB Relationships settings.
     */
    public function build(): array
    {
        if ($this->from === null || $this->to === null) {
            throw new LogicException("The relationship '{$this->id}' needs a from and a to side.");
        }

        $from = $this->from->build($this->to);
        $to = $this->to->build($this->from);

        if ($this->reciprocal) {
            $this->ensureReciprocal($from, $to);
        }

        return array_filter([
            'id' => $this->id,
            'from' => $from,
            'to' => $to,
            'reciprocal' => $this->reciprocal ?: null,
        ], fn ($value) => $value !== null);
    }

    /**
     * A reciprocal relationship connects objects of the same type, and only displays the from side.
     *
     * @param  array  $from  The settings of the from side.
     * @param  array  $to  The settings of the to side, which hold the field of the from side.
     */
    protected function ensureReciprocal(array $from, array $to): void
    {
        $objects = fn (array $side) => array_intersect_key($side, array_flip(['object_type', 'post_type', 'taxonomy']));

        if ($objects($from) !== $objects($to)) {
            throw new LogicException("The reciprocal relationship '{$this->id}' must connect objects of the same type.");
        }

        if (isset($to['meta_box']) || isset($to['admin_column']) || isset($from['field'])) {
            throw new LogicException("The reciprocal relationship '{$this->id}' is displayed with the from side only: set the meta box, field and admin column there.");
        }
    }
}
