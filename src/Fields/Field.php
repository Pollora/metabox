<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields;

use Pollora\Metabox\Contract\Renderable;
use Pollora\Metabox\Fields\Settings\AdminColumn;
use Pollora\Metabox\Fields\Settings\Attributes;
use Pollora\Metabox\Fields\Settings\Clonable;
use Pollora\Metabox\Fields\Settings\Columns;
use Pollora\Metabox\Fields\Settings\ConditionalLogic;
use Pollora\Metabox\Fields\Settings\DefaultValue;
use Pollora\Metabox\Fields\Settings\Description;
use Pollora\Metabox\Fields\Settings\FieldAccess;
use Pollora\Metabox\Fields\Settings\Multiple;
use Pollora\Metabox\Fields\Settings\Placeholder;
use Pollora\Metabox\Fields\Settings\Required;
use Pollora\Metabox\Fields\Settings\Saving;
use Pollora\Metabox\Fields\Settings\Sortable;
use Pollora\Metabox\Fields\Settings\Tab;
use Pollora\Metabox\Fields\Settings\Tooltip;
use Pollora\Metabox\Fields\Settings\Visibility;
use Pollora\Metabox\Fields\Settings\Wrapper;
use Pollora\Metabox\Fields\Utils\Builder;

/**
 * Abstract class for all fields.
 *
 * @phpstan-consistent-constructor
 */
abstract class Field implements Renderable
{
    use AdminColumn;

    /**
     * Trait for custom HTML attributes.
     */
    use Attributes;

    use Builder;

    /**
     * Trait for cloning the field.
     */
    use Clonable;

    use Columns;
    use ConditionalLogic;

    /**
     * Trait for setting a default value for the field.
     */
    use DefaultValue;

    /**
     * Trait for adding a description to the field.
     */
    use Description;

    /**
     * Trait for setting the field access.
     */
    use FieldAccess;

    /**
     * Trait for setting whether the field is multiple or not.
     */
    use Multiple;

    /**
     * Trait for setting a placeholder for the field.
     */
    use Placeholder;

    /**
     * Trait for setting whether the field is required or not.
     */
    use Required;

    /**
     * Trait for how the field value is saved.
     */
    use Saving;

    /**
     * Trait for setting the sort order of the field.
     */
    use Sortable;

    use Tab;
    use Tooltip;

    /**
     * Trait for hiding the field from the REST API and front-end forms.
     */
    use Visibility;

    /**
     * Trait for the wrapper class and the HTML before / after the field.
     */
    use Wrapper;

    /**
     * The type of the field.
     */
    protected string $type = 'text';

    /**
     * The settings for the field.
     *
     * @var array<string, mixed>
     */
    protected array $settings = [];

    /**
     * The name of the input, when it differs from the field ID.
     */
    protected string $field_name;

    /**
     * Constructor for the Field class.
     *
     * @param  string  $name  The name of the field.
     * @param  string  $id  The id of the field.
     */
    public function __construct(
        /**
         * The name of the field.
         */
        protected string $name,
        /**
         * The id of the field.
         */
        protected string $id
    ) {}

    /**
     * Create a field.
     *
     * @param  string  $name  The label of the field.
     * @param  string  $id  The ID of the field, used as the meta key.
     */
    public static function make(string $name, string $id): static
    {
        return new static($name, $id);
    }

    /**
     * Set the name of the input, when it must differ from the field ID.
     */
    public function fieldName(string $fieldName): static
    {
        $this->field_name = $fieldName;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setting(string $key, mixed $value): static
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * Get the custom settings for the field.
     *
     * @return array<string, mixed> The custom settings.
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
