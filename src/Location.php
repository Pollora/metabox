<?php

declare(strict_types=1);

namespace Pollora\Metabox;

/**
 * This class is used to define conditions for a conditional location of a fieldset.
 *
 * @phpstan-consistent-constructor
 */
class Location
{
    /**
     * @var array<string, mixed>
     *
     * Stores the conditions for the location.
     */
    protected array $conditions = [];

    /**
     * Location constructor.
     *
     * Initializes a new instance of the Location class with the specified type and values.
     *
     * @param  string  $type
     *                        The type of condition to be applied.
     * @param  string|array<mixed>  $values
     *                                       The values associated with the condition.
     */
    public function __construct(protected string $type, string|array $values)
    {
        $this->conditions[$type] = $values;
    }

    /**
     * Location::where
     *
     * Creates a new instance of the Location class with the specified type and values.
     *
     * @param  string  $type
     *                        The type of condition to be applied.
     * @param  string|array<mixed>  $values
     *                                       The values associated with the condition.
     * @return static
     *                Returns a new instance of the Location class with the specified type and values.
     */
    public static function where(string $type, string|array $values): static
    {
        return new static($type, $values);
    }

    /**
     * Location::default
     *
     * Creates a new instance of the Location class with the default type and values.
     *
     * @return static
     *                Returns a new instance of the Location class with the default type and values.
     */
    public static function default(): static
    {
        return new static('post_types', ['post']);
    }

    /**
     * Show the meta box on the edit screen of the given post types.
     *
     * @param  string|array<mixed>  $postTypes
     */
    public static function postTypes(string|array $postTypes): static
    {
        return new static('post_types', (array) $postTypes);
    }

    /**
     * Show the meta box on the edit screen of terms of the given taxonomies (MB Term Meta).
     *
     * @param  string|array<mixed>  $taxonomies
     */
    public static function taxonomies(string|array $taxonomies): static
    {
        return new static('taxonomies', (array) $taxonomies);
    }

    /**
     * Show the meta box on the given settings pages (MB Settings Page), in the given tab
     * when the pages have tabs.
     *
     * @param  string|array<mixed>  $settingsPages
     */
    public static function settingsPages(string|array $settingsPages, ?string $tab = null): static
    {
        $location = new static('settings_pages', (array) $settingsPages);

        return $tab === null ? $location : $location->andWhere('tab', $tab);
    }

    /**
     * Show the meta box on the add and edit screens of the given custom models (MB Custom Table).
     * The field values are stored in the table of the models declared with MetaboxModel.
     *
     * @param  string|array<mixed>  $models
     */
    public static function models(string|array $models): static
    {
        return new static('models', (array) $models);
    }

    /**
     * Show the meta box on the user profile screen (MB User Meta).
     */
    public static function user(): static
    {
        return new static('type', 'user');
    }

    /**
     * Show the meta box on the comment edit screen (MB Comment Meta).
     */
    public static function comment(): static
    {
        return new static('type', 'comment');
    }

    /**
     * Add another condition to the location, e.g. a settings page tab.
     *
     * @param  string|array<mixed>  $values
     */
    public function andWhere(string $type, string|array $values): static
    {
        $this->conditions[$type] = $values;

        return $this;
    }

    /**
     * Location::get
     *
     * Retrieves the conditions associated with the location.
     *
     * @return array<string, mixed>
     *                              Returns an array containing the conditions associated with the location.
     */
    public function get(): array
    {
        return $this->conditions;
    }
}
