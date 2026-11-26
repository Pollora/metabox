<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields;

use LogicException;
use Pollora\Metabox\Enums\EntityFieldType;
use Pollora\Metabox\Fields\Settings\Ajax;
use Pollora\Metabox\Fields\Settings\ToggleAll;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * Post field class for creating fields that allow selecting posts.
 */
class Post extends Field
{
    use Ajax;
    use ToggleAll;

    /**
     * The type of field.
     */
    protected string $type = 'post';

    /**
     * The type of post to select.
     *
     * @var string|array<mixed>
     */
    protected string|array $post_type;

    /**
     * The arguments to pass to the WP_Query function.
     *
     * @var array<mixed>
     */
    protected array $query_args;

    /**
     * Whether the field should allow selecting parent posts.
     */
    protected bool $parent;

    /**
     * The type of field.
     */
    protected string $field_type = 'select';

    /**
     * Whether a button opens a form to create a new post.
     */
    protected bool $add_new;

    /**
     * Set the type of post to select.
     *
     * @param  string|array<mixed>  $postType  The type of post to select.
     */
    public function postType(string|array $postType): static
    {
        $this->post_type = $postType;

        return $this;
    }

    /**
     * Set the arguments to pass to the WP_Query function.
     *
     * @param  array<mixed>  $queryArgs  The arguments to pass to the WP_Query function.
     */
    public function queryArgs(array $queryArgs): static
    {
        $this->query_args = $queryArgs;

        return $this;
    }

    /**
     * Set whether the field should allow selecting parent posts.
     *
     * @param  bool  $parent  Whether the field should allow selecting parent posts.
     */
    public function setAsParent(bool $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Set the type of field.
     *
     * @param  EntityFieldType|string  $fieldType  The type of field.
     */
    public function fieldType(EntityFieldType|string $fieldType): static
    {
        $this->field_type = OptionValidation::check($fieldType, EntityFieldType::class);

        return $this;
    }

    /**
     * Show a button opening a form to create a new post of the post type. Requires a single post type.
     */
    public function addNew(bool $addNew = true): static
    {
        $this->add_new = $addNew;

        return $this;
    }

    /**
     * Build the field and return its settings.
     *
     * @return array<mixed>
     */
    public function build(): array
    {
        if (($this->add_new ?? false) && count((array) ($this->post_type ?? 'post')) !== 1) {
            throw new LogicException('addNew() requires a single post type: Meta Box does not show the button for several post types.');
        }

        return parent::build();
    }
}
