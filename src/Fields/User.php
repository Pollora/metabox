<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields;

use Pollora\Metabox\Enums\EntityFieldType;
use Pollora\Metabox\Fields\Settings\Ajax;
use Pollora\Metabox\Fields\Settings\ToggleAll;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * User field class for creating fields that allow selecting users.
 */
class User extends Field
{
    use Ajax;
    use ToggleAll;

    protected string $type = 'user';

    /** @var array<mixed> */
    protected array $query_args;

    protected string $field_type = 'select';

    /**
     * The user property used as the option label.
     */
    protected string $display_field = 'display_name';

    /**
     * Whether a button opens a form to create a new user.
     */
    protected bool $add_new;

    /**
     * @param  array<mixed>  $queryArgs
     */
    public function queryArgs(array $queryArgs): static
    {
        $this->query_args = $queryArgs;

        return $this;
    }

    public function fieldType(EntityFieldType|string $fieldType): static
    {
        $this->field_type = OptionValidation::check($fieldType, EntityFieldType::class);

        return $this;
    }

    /**
     * Set the user property used as the option label.
     *
     * @param  string  $displayField  A WP_User property, e.g. display_name or user_email.
     */
    public function displayField(string $displayField): static
    {
        $this->display_field = $displayField;

        return $this;
    }

    /**
     * Show a button opening a form to create a new user.
     */
    public function addNew(bool $addNew = true): static
    {
        $this->add_new = $addNew;

        return $this;
    }
}
