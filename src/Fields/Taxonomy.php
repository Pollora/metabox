<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields;

use Pollora\Metabox\Enums\EntityFieldType;
use Pollora\Metabox\Fields\Settings\Ajax;
use Pollora\Metabox\Fields\Settings\ToggleAll;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * Taxonomy field class for creating fields that allow selecting terms.
 */
class Taxonomy extends Field
{
    use Ajax;
    use ToggleAll;

    /**
     * The type of field.
     */
    protected string $type = 'taxonomy';

    /**
     * The taxonomies to select terms from.
     *
     * @var string|array<mixed>
     */
    protected string|array $taxonomy = 'category';

    /**
     * The arguments to pass to the WP_Term_Query function.
     *
     * @var array<mixed>
     */
    protected array $query_args;

    /**
     * Allow users to create a new term when submitting the post.
     */
    protected bool $add_new;

    /**
     * Remove the default WordPress taxonomy meta box. Only works with the classic editor.
     */
    protected bool $remove_default;

    /**
     * The type of field.
     */
    protected string $field_type = 'select';

    /**
     * Set the taxonomies to select terms from.
     *
     * @param  string|array<mixed>  $taxonomies  A taxonomy slug or a list of taxonomy slugs.
     */
    public function taxonomies(string|array $taxonomies): static
    {
        $this->taxonomy = $taxonomies;

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
     * Allow users to create a new term when submitting the post.
     *
     * @param  bool  $addNew  Defaults to true.
     */
    public function addNew(bool $addNew = true): static
    {
        $this->add_new = $addNew;

        return $this;
    }

    /**
     * Remove the default WordPress taxonomy meta box. Only works with the classic editor.
     *
     * @param  bool  $removeDefault  Defaults to true.
     */
    public function removeDefault(bool $removeDefault = true): static
    {
        $this->remove_default = $removeDefault;

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
}
