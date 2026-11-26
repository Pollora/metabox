<?php

declare(strict_types=1);

namespace Pollora\Metabox\Relationships;

use InvalidArgumentException;
use Pollora\Metabox\Enums\AdminColumnLink;
use Pollora\Metabox\Enums\BoxStyle;
use Pollora\Metabox\Enums\Context;
use Pollora\Metabox\Enums\Priority;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * One side of a relationship: the objects it connects, and what is displayed
 * on their screens (MB Relationships).
 *
 * Every setting of a side applies to the screens of its objects: the meta box
 * and its field are displayed on their edit screen, the admin column in their list.
 *
 * @phpstan-consistent-constructor
 */
class Side
{
    /**
     * Whether each object of this side connects to one object at most.
     */
    protected bool $hasOne = false;

    /**
     * The settings of the meta box displayed on the edit screen of the objects.
     *
     * @var array<mixed>
     */
    protected array $metaBox = [];

    /**
     * The settings of the field selecting the connected objects, in the meta box.
     *
     * @var array<mixed>
     */
    protected array $field = [];

    /**
     * The admin column listing the connected objects, or true for a default column.
     *
     * @var bool|array<mixed>
     */
    protected bool|array $adminColumn = false;

    /**
     * @param  string  $objectType  'post', 'term' or 'user'.
     * @param  string|null  $subtype  The post type or the taxonomy.
     */
    protected function __construct(protected string $objectType, protected ?string $subtype = null) {}

    /**
     * The posts of a post type.
     */
    public static function posts(string $postType = 'post'): static
    {
        return new static('post', $postType);
    }

    /**
     * The terms of a taxonomy.
     */
    public static function terms(string $taxonomy): static
    {
        return new static('term', $taxonomy);
    }

    /**
     * The users.
     */
    public static function users(): static
    {
        return new static('user');
    }

    /**
     * Connect each object of this side to one object of the other side at most,
     * e.g. a product to one brand. Set it on both sides for a one-to-one relationship.
     */
    public function hasOne(bool $hasOne = true): static
    {
        $this->hasOne = $hasOne;

        return $this;
    }

    /**
     * Set the meta box displayed on the edit screen of the objects of this side.
     *
     * @param  string|null  $title  Defaults to "Connects To" on the from side and "Connected From" on the to side.
     * @param  string|Context|null  $context  Defaults to 'side'.
     * @param  string|Priority|null  $priority  Defaults to 'low'.
     * @param  bool  $hidden  Do not display the meta box: the connections are managed with code.
     */
    public function metaBox(
        ?string $title = null,
        string|Context|null $context = null,
        string|Priority|null $priority = null,
        bool $closed = false,
        string|BoxStyle|null $style = null,
        ?string $class = null,
        bool $hidden = false,
    ): static {
        $this->metaBox = array_filter([
            'title' => $title,
            'context' => $context === null ? null : OptionValidation::check($context, Context::class),
            'priority' => $priority === null ? null : OptionValidation::check($priority, Priority::class),
            'closed' => $closed ?: null,
            'style' => $style === null ? null : OptionValidation::check($style, BoxStyle::class),
            'class' => $class,
            'hidden' => $hidden ?: null,
        ], fn ($value) => $value !== null);

        return $this;
    }

    /**
     * Set the field selecting the connected objects, displayed in the meta box of this side.
     *
     * @param  string|null  $label  Defaults to the plural name of the connected post type or taxonomy, or "Users".
     * @param  int|null  $max  The maximum number of connected objects.
     * @param  array<mixed>  $queryArgs  Arguments of the query listing the connected objects, passed to WP_Query, get_terms() or get_users().
     */
    public function field(?string $label = null, ?string $placeholder = null, ?int $max = null, array $queryArgs = []): static
    {
        if ($max !== null && $max < 1) {
            throw new InvalidArgumentException("The maximum number of connected objects must be at least 1, {$max} given.");
        }

        $this->field = array_filter([
            'name' => $label,
            'placeholder' => $placeholder,
            'max_clone' => $max,
            'query_args' => $queryArgs,
        ], fn ($value) => $value !== null && $value !== []);

        return $this;
    }

    /**
     * List the connected objects in a column of the admin list of this side. Without
     * arguments, the column is added at the end.
     *
     * @param  string|null  $before  Insert the column before this column ID, e.g. 'title'.
     * @param  string|null  $after  Insert the column after this column ID.
     * @param  string|null  $replace  Replace this column ID.
     * @param  string|null  $title  The column title. Defaults to the meta box title.
     * @param  string|AdminColumnLink|false|null  $link  Link the connected objects to their 'view' page (default) or 'edit' screen, or false for no link.
     */
    public function adminColumn(
        ?string $before = null,
        ?string $after = null,
        ?string $replace = null,
        ?string $title = null,
        string|AdminColumnLink|false|null $link = null,
    ): static {
        $positions = array_filter(['before' => $before, 'after' => $after, 'replace' => $replace], fn ($column) => $column !== null);

        if (count($positions) > 1) {
            throw new InvalidArgumentException('An admin column can only be placed before, after or instead of one column.');
        }

        $settings = array_filter([
            'position' => $positions === [] ? null : key($positions).' '.current($positions),
            'title' => $title,
            'link' => $link === null || $link === false ? $link : OptionValidation::check($link, AdminColumnLink::class),
        ], fn ($value) => $value !== null);

        $this->adminColumn = $settings === [] ? true : $settings;

        return $this;
    }

    /**
     * Build the Meta Box settings of this side.
     *
     * Meta Box displays the field settings of a side on the screen of the other
     * side: they are taken from the other side, where they are displayed.
     *
     * @param  self  $other  The other side of the relationship.
     * @return array<string, mixed>
     */
    public function build(self $other): array
    {
        $subtypeKey = ['post' => 'post_type', 'term' => 'taxonomy', 'user' => null][$this->objectType];

        return array_filter([
            'object_type' => $this->objectType,
            ...($subtypeKey === null ? [] : [$subtypeKey => $this->subtype]),
            'has_one_relationship' => $this->hasOne ?: null,
            'meta_box' => $this->metaBox,
            'field' => $other->field,
            'admin_column' => $this->adminColumn ?: null,
        ], fn ($value) => $value !== null && $value !== []);
    }
}
