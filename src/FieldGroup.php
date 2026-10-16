<?php

declare(strict_types=1);

namespace Pollora\Metabox;

use Exception;
use InvalidArgumentException;
use LogicException;
use Pollora\Metabox\Contract\Renderable;
use Pollora\Metabox\CustomTable\TableName;
use Pollora\Metabox\Enums\BoxStyle;
use Pollora\Metabox\Enums\Context;
use Pollora\Metabox\Enums\Priority;
use Pollora\Metabox\Enums\TabStyle;
use Pollora\Metabox\Enums\ToggleType;
use Pollora\Metabox\Fields\Field;
use Pollora\Metabox\Fields\Settings\ConditionalLogic;
use Pollora\Metabox\Rules\Rule;
use Pollora\Metabox\Rules\RuleGroup;
use Pollora\Metabox\Transformer\EmptyValueFilter;
use Pollora\Metabox\Transformer\FieldTransformer;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * Field group: fields registered together with Meta Box, displayed as a meta box on edit screens.
 *
 * @phpstan-consistent-constructor
 */
class FieldGroup implements Renderable
{
    use ConditionalLogic;
    use FieldTransformer;

    /**
     * The context of the field group.
     */
    protected string $context;

    /**
     * The fields within the field group.
     */
    protected array $fields = [];

    /**
     * The tabs within the field group.
     */
    protected array $tabs = [];

    /**
     * The location of the field group.
     */
    protected ?Location $location = null;

    /**
     * The priority of the field group.
     */
    protected string|int|null $priority = null;

    /**
     * The style of the field group.
     */
    protected string $style;

    /**
     * Whether the field group is initially closed.
     */
    protected bool $closed;

    /**
     * Whether the field group is initially hidden.
     */
    protected bool $default_hidden;

    /**
     * Whether the field group autosaves its content.
     */
    protected bool $autosave;

    /**
     * Whether the field group opens a media modal when clicked.
     */
    protected bool $media_modal;

    /**
     * The class of the field group.
     */
    protected ?string $class = null;

    /**
     * The description of the field group.
     */
    protected string $description;

    /**
     * The type of the field group.
     */
    protected string $type;

    /**
     * The style of the tabs.
     */
    protected string $tab_style;

    /**
     * Whether the field group wrapper is displayed around the tabs.
     */
    protected bool $tab_wrapper;

    /**
     * The ID of the tab active by default.
     */
    protected string $tab_default_active;

    /**
     * Whether the last active tab is remembered.
     */
    protected bool $tab_remember;

    /**
     * The validation rules and messages, keyed by input name.
     */
    protected array $validation;

    /**
     * Whether the field values are tracked in post revisions (MB Revision).
     */
    protected bool $revision;

    /**
     * Where the field values are stored, e.g. custom_table.
     */
    protected string $storage_type;

    /**
     * The custom table storing the field values (MB Custom Table).
     */
    protected ?TableName $table = null;

    /**
     * How conditional logic shows and hides elements.
     */
    protected string $toggle_type;

    /**
     * The rules registering the field group (MB Include Exclude).
     */
    protected array $include;

    /**
     * The rules not registering the field group (MB Include Exclude).
     */
    protected array $exclude;

    /**
     * The rules displaying the field group (MB Show Hide).
     */
    protected array $show;

    /**
     * The rules hiding the field group (MB Show Hide).
     */
    protected array $hide;

    /**
     * The columns holding several fields (Meta Box Columns).
     */
    protected array $columns = [];

    /**
     * The geolocation settings (MB Geolocation).
     */
    protected bool|array $geo;

    /**
     * The Customizer panel of the section, or '' for a top-level section (MB Settings Page).
     */
    protected string $panel;

    /**
     * The option saving the values of a Customizer section.
     */
    protected ?string $option_name = null;

    /**
     * The custom settings for the field group.
     */
    protected array $settings = [];

    /**
     * Construct a new field group.
     *
     * @param  string  $title  The title of the field group.
     * @param  string  $id  The unique identifier of the field group.
     */
    public function __construct(protected string $id, protected string $title)
    {
        if (! function_exists('add_filter')) {
            throw new Exception('Metabox Maker requires WordPress to be loaded.');
        }

        if (! doing_filter('rwmb_meta_boxes')) {
            add_filter('rwmb_meta_boxes', function ($meta_boxes) {
                $meta_boxes[] = $this->build();

                return $meta_boxes;
            });
        }
    }

    /**
     * Create a field group. It is registered with Meta Box automatically.
     *
     * @param  string  $title  The title of the field group.
     * @param  string  $id  The ID of the field group.
     */
    public static function make(string $title, string $id): static
    {
        return new static($id, $title);
    }

    /**
     * Set the context of the field group.
     *
     * @param  string|Context  $context  The context of the field group.
     */
    public function context(string|Context $context): static
    {
        $this->context = OptionValidation::check($context, Context::class);

        return $this;
    }

    /**
     * Add fields to the field group.
     *
     * @param  array<Field>  $fields  The fields to add.
     */
    public function fields(array $fields): static
    {
        $this->buildFieldset($fields);

        return $this;
    }

    /**
     * Set the tabs of the field group.
     *
     * @param  array  $tabs  The tabs of the field group.
     */
    public function tabs(array $tabs): static
    {
        $this->tabs = $tabs;

        return $this;
    }

    /**
     * Set the priority of the field group: 'high' or 'low', or the position of the
     * section in the Customizer, e.g. 30.
     *
     * @param  string|int|Priority  $priority  The priority of the field group.
     */
    public function priority(string|int|Priority $priority): static
    {
        $this->priority = is_int($priority) ? $priority : OptionValidation::check($priority, Priority::class);

        return $this;
    }

    /**
     * Set the location of the field group.
     *
     * @param  Location  $location  The location of the field group.
     */
    public function location(Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Set the style of the field group.
     *
     * @param  string|BoxStyle  $style  The style of the field group.
     */
    public function style(string|BoxStyle $style): static
    {
        $this->style = OptionValidation::check($style, BoxStyle::class);

        return $this;
    }

    /**
     * Set whether the field group is initially closed.
     *
     * @param  bool  $closed  Whether the field group is initially closed.
     */
    public function closed(bool $closed): static
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Set whether the field group is initially hidden.
     *
     * @param  bool  $defaultHidden  Whether the field group is initially hidden.
     */
    public function defaultHidden(bool $defaultHidden): static
    {
        $this->default_hidden = $defaultHidden;

        return $this;
    }

    /**
     * Set whether the field group autosaves its content.
     *
     * @param  bool  $autosave  Whether the field group autosaves its content.
     */
    public function autosave(bool $autosave): static
    {
        $this->autosave = $autosave;

        return $this;
    }

    /**
     * Set whether the field group opens a media modal when clicked.
     *
     * @param  bool  $mediaModal  Whether the field group opens a media modal when clicked.
     */
    public function mediaModal(bool $mediaModal): static
    {
        $this->media_modal = $mediaModal;

        return $this;
    }

    /**
     * Set the class of the field group.
     *
     * @param  string|null  $class  The class of the field group.
     */
    public function class(?string $class): static
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Set the description of the field group.
     *
     * @param  string  $description  The description of the field group.
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the type of the field group.
     *
     * @param  string  $type  The type of the field group.
     */
    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the style of the tabs.
     */
    public function tabStyle(string|TabStyle $style): static
    {
        $this->tab_style = OptionValidation::check($style, TabStyle::class);

        return $this;
    }

    /**
     * Set whether the field group wrapper is displayed around the tabs.
     */
    public function tabWrapper(bool $wrapper = true): static
    {
        $this->tab_wrapper = $wrapper;

        return $this;
    }

    /**
     * Set the ID of the tab active by default.
     */
    public function tabDefaultActive(string $tabId): static
    {
        $this->tab_default_active = $tabId;

        return $this;
    }

    /**
     * Remember the last active tab when saving.
     */
    public function tabRemember(bool $remember = true): static
    {
        $this->tab_remember = $remember;

        return $this;
    }

    /**
     * Set the validation rules (jQuery Validation) and their error messages.
     *
     * Rules and messages are keyed by input name: usually the field ID, but e.g.
     * 'my_taxonomy[]' for a checkbox list or '_file_my_files[]' for file fields.
     *
     * @param  array  $rules  e.g. ['email' => ['required' => true, 'minlength' => 7]]
     * @param  array  $messages  e.g. ['email' => ['required' => 'Email is required']]
     */
    public function validation(array $rules, array $messages = []): static
    {
        $this->validation = array_filter(['rules' => $rules, 'messages' => $messages]);

        return $this;
    }

    /**
     * Track the field values in post revisions (MB Revision).
     */
    public function revision(bool $revision = true): static
    {
        $this->revision = $revision;

        return $this;
    }

    /**
     * Set where the field values are stored, e.g. custom_table, or post_meta for blocks.
     */
    public function storageType(string $storageType): static
    {
        $this->storage_type = $storageType;

        return $this;
    }

    /**
     * Store the field values in a custom table (MB Custom Table), named without the
     * WordPress table prefix, which is added.
     *
     * @param  string  $table  The table name, or a class with a getTable() method, such as an Eloquent model.
     * @param  bool  $prefix  Set to false for an existing table without the prefix.
     */
    public function customTable(string $table, bool $prefix = true): static
    {
        $this->storage_type = 'custom_table';
        $this->table = new TableName($table, $prefix);

        return $this;
    }

    /**
     * Set how conditional logic shows and hides the fields of the field group.
     */
    public function toggleType(string|ToggleType $toggleType): static
    {
        $this->toggle_type = OptionValidation::check($toggleType, ToggleType::class);

        return $this;
    }

    /**
     * Only register the field group when the rule matches, e.g. Rule::template('landing.php'),
     * evaluated when the edit screen loads (MB Include Exclude).
     */
    public function include(Rule|RuleGroup $rule): static
    {
        $this->include = $this->buildRules($rule, 'include');

        return $this;
    }

    /**
     * Do not register the field group when the rule matches (MB Include Exclude).
     */
    public function exclude(Rule|RuleGroup $rule): static
    {
        $this->exclude = $this->buildRules($rule, 'exclude');

        return $this;
    }

    /**
     * Display the field group when the rule matches, updated live while editing,
     * e.g. when the page template changes (MB Show Hide).
     */
    public function show(Rule|RuleGroup $rule): static
    {
        $this->show = $this->buildRules($rule, 'show');

        return $this;
    }

    /**
     * Hide the field group when the rule matches, updated live while editing (MB Show Hide).
     */
    public function hide(Rule|RuleGroup $rule): static
    {
        $this->hide = $this->buildRules($rule, 'hide');

        return $this;
    }

    /**
     * Fill the fields from the address selected in an autocomplete address field (MB Geolocation).
     *
     * The autocomplete field is a text field with an ID starting with 'address', and
     * the fields named after an address component, e.g. 'locality', are filled.
     *
     * @param  string|null  $apiKey  The Google Maps API key, unless a Google Maps field sets it. Without a key, OpenStreetMap is used.
     * @param  array  $types  The Google place types suggested, e.g. ['establishment'] or ['(cities)'].
     * @param  string|array<string>  $countries  The ISO 3166-1 alpha-2 codes of the countries the suggestions are restricted to, at most 5 (Google Maps).
     */
    public function geolocation(?string $apiKey = null, array $types = [], string|array $countries = []): static
    {
        $countries = array_map('strtolower', (array) $countries);

        foreach ($countries as $country) {
            if (! preg_match('/^[a-z]{2}$/', $country)) {
                throw new InvalidArgumentException("'{$country}' is not an ISO 3166-1 alpha-2 country code.");
            }
        }

        if (count($countries) > 5) {
            throw new InvalidArgumentException('Google Maps restricts the suggestions to 5 countries at most.');
        }

        foreach ($types as $type) {
            if (! is_string($type) || $type === '') {
                throw new InvalidArgumentException('The place types must be non-empty strings.');
            }
        }

        $geo = array_filter([
            'api_key' => $apiKey,
            'types' => array_values($types),
            'componentRestrictions' => $countries === [] ? null : ['country' => count($countries) === 1 ? $countries[0] : $countries],
        ]);

        $this->geo = $geo === [] ? true : $geo;

        return $this;
    }

    /**
     * Display the field group as a section of the Customizer instead of an edit screen,
     * at the top level or in the given panel (MB Settings Page).
     *
     * @param  string|null  $panel  The ID of the panel holding the section.
     * @param  string|null  $optionName  The option saving the values. Defaults to the theme mods of the active theme.
     */
    public function customizer(?string $panel = null, ?string $optionName = null): static
    {
        $this->panel = $panel ?? '';
        $this->option_name = $optionName;

        return $this;
    }

    /**
     * Set a custom setting for the field group.
     *
     * @param  string  $key  The setting key.
     * @param  mixed  $value  The setting value.
     */
    public function setting(string $key, mixed $value): static
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * Get the custom settings for the field group.
     *
     * @return array The custom settings.
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Build the field group and return its settings.
     *
     * @return array The settings of the field group.
     */
    public function build(): array
    {
        if (! $this->location && ! isset($this->panel)) {
            $this->location = Location::default();
        }

        if (is_int($this->priority) && ! isset($this->panel) && ! isset($this->location?->get()['settings_pages'])) {
            throw new LogicException('An integer priority sets the position of a Customizer section: use customizer() or a settings page location, or the high or low priority.');
        }

        $metaboxData = EmptyValueFilter::filter(get_object_vars($this));
        $metaboxData = array_merge($metaboxData, $this->customTableSettings());

        // Remove the settings array and location from the field group data
        unset($metaboxData['settings'], $metaboxData['location']);

        // Merge custom settings if they exist
        if (! empty($this->settings)) {
            $metaboxData = array_merge($metaboxData, $this->settings);
        }

        return $metaboxData + ($this->location?->get() ?? []);
    }

    /**
     * The custom table settings: the table set with customTable(), or the table of the models
     * the field group is displayed on.
     */
    protected function customTableSettings(): array
    {
        if ($this->table !== null) {
            return ['table' => $this->table->resolve()];
        }

        $models = (array) ($this->location?->get()['models'] ?? []);

        if ($models === []) {
            return [];
        }

        $tables = array_unique(array_map(function (string $name) {
            $model = MetaboxModel::get($name)
                ?? throw new LogicException("The model '{$name}' is not declared with MetaboxModel: call customTable() with its table.");

            return $model->getTable();
        }, $models));

        if (count($tables) > 1) {
            throw new LogicException('The models of a field group must share the same table: '.implode(', ', $tables).'.');
        }

        return ['storage_type' => 'custom_table', 'table' => $tables[0]];
    }

    /**
     * Convert a rule or a group of rules to the Meta Box settings.
     */
    protected function buildRules(Rule|RuleGroup $rule, string $method): array
    {
        return ($rule instanceof Rule ? Rule::all($rule) : $rule)->toArray($method);
    }
}
