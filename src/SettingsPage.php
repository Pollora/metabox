<?php

declare(strict_types=1);

namespace Pollora\Metabox;

use InvalidArgumentException;
use Pollora\Metabox\Contract\Renderable;
use Pollora\Metabox\Enums\IconType;
use Pollora\Metabox\Enums\SettingsPageStyle;
use Pollora\Metabox\Enums\TabStyle;
use Pollora\Metabox\Exception\WordPressNotLoadedException;
use Pollora\Metabox\Transformer\EmptyValueFilter;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * @phpstan-consistent-constructor
 */
class SettingsPage implements Renderable
{
    /**
     * Menu position
     */
    protected ?int $position = null;

    /**
     * Default first submenu title
     */
    protected ?string $menu_title = null;

    /**
     * Default first submenu title
     */
    protected ?string $submenu_title = null;

    /**
     * Icon type (dashicons, fontawesome, svg, url)
     */
    protected string $icon_type;

    /**
     * Icon value
     */
    protected ?string $icon = null;

    /**
     * Icon SVG
     */
    protected ?string $icon_svg = null;

    /**
     * Icon URL
     */
    protected ?string $icon_url = null;

    /**
     * Parent menu slug
     */
    protected ?string $parent = null;

    /**
     * Required capability
     */
    protected string $capability = 'edit_theme_options';

    /**
     * Custom CSS class
     */
    protected ?string $class = null;

    /**
     * Page style
     */
    protected string $style = 'boxes';

    /**
     * Number of columns
     */
    protected int $columns = 1;

    /**
     * Tabs configuration
     *
     * @var array<mixed>
     */
    protected array $tabs = [];

    /**
     * Tab style
     */
    protected string $tab_style = 'default';

    /**
     * Custom submit button text
     */
    protected ?string $submit_button = null;

    /**
     * Custom save message
     */
    protected ?string $message = null;

    /**
     * Help tabs content
     *
     * @var array<mixed>
     */
    protected array $help_tabs = [];

    /**
     * Show in customizer
     */
    protected bool $customizer = false;

    /**
     * Show only in customizer
     */
    protected bool $customizer_only = false;

    /**
     * Network-wide settings
     */
    protected bool $network = false;

    /**
     * Option name for storing settings
     */
    protected ?string $option_name = null;

    /**
     * The custom settings for the settings page.
     *
     * @var array<string, mixed>
     */
    protected array $settings = [];

    /**
     * Constructor for the SettingsPage class.
     *
     * @param  string  $page_title  The title of the settings page
     * @param  string  $id  The unique identifier for the settings page
     */
    public function __construct(protected string $page_title, protected string $id)
    {
        if (! function_exists('add_filter')) {
            throw WordPressNotLoadedException::forMetaboxMaker();
        }

        $this->menu_title = $page_title;

        if (! doing_filter('mb_settings_pages')) {
            add_filter('mb_settings_pages', function ($settings_pages) {
                $settings_pages[] = $this->build();

                return $settings_pages;
            });
        }
    }

    /**
     * Create a settings page. It is registered with MB Settings Page automatically.
     *
     * @param  string  $pageTitle  The title of the page.
     * @param  string  $id  The ID of the page, used in its URL.
     */
    public static function make(string $pageTitle, string $id): static
    {
        return new static($pageTitle, $id);
    }

    public function position(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function menuTitle(string $title): static
    {
        $this->menu_title = $title;

        return $this;
    }

    public function submenuTitle(string $title): static
    {
        $this->submenu_title = $title;

        return $this;
    }

    public function iconType(string|IconType $type): static
    {
        $this->icon_type = OptionValidation::check($type, IconType::class);

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function iconSvg(string $svg): static
    {
        $this->icon_svg = $svg;

        return $this;
    }

    public function iconUrl(string $url): static
    {
        $this->icon_url = $url;

        return $this;
    }

    public function parent(string $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function capability(string $capability): static
    {
        $this->capability = $capability;

        return $this;
    }

    public function class(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function style(string|SettingsPageStyle $style): static
    {
        $this->style = OptionValidation::check($style, SettingsPageStyle::class);

        return $this;
    }

    public function columns(int $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param  array<mixed>  $tabs
     */
    public function tabs(array $tabs): static
    {
        $this->tabs = $tabs;

        return $this;
    }

    /**
     * Add a tab to the page. Meta boxes are displayed in it with Location::settingsPages($page, tab: $id).
     *
     * @param  string  $id  The ID of the tab.
     * @param  string  $label  The label of the tab.
     * @param  string|null  $icon  A Dashicons or icon font class, e.g. 'dashicons-admin-generic', or an image URL.
     */
    public function tab(string $id, string $label, ?string $icon = null): static
    {
        if (isset($this->tabs[$id])) {
            throw new InvalidArgumentException("The settings page already has a tab '{$id}'.");
        }

        $this->tabs[$id] = $icon === null ? $label : ['label' => $label, 'icon' => $icon];

        return $this;
    }

    public function tabStyle(string|TabStyle $style): static
    {
        $this->tab_style = OptionValidation::check($style, TabStyle::class);

        return $this;
    }

    public function submitButton(string $text): static
    {
        $this->submit_button = $text;

        return $this;
    }

    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param  array<mixed>  $tabs
     */
    public function helpTabs(array $tabs): static
    {
        $this->help_tabs = $tabs;

        return $this;
    }

    public function customizer(bool $enabled = true): static
    {
        $this->customizer = $enabled;

        return $this;
    }

    public function customizerOnly(bool $enabled = true): static
    {
        $this->customizer_only = $enabled;

        return $this;
    }

    public function network(bool $enabled = true): static
    {
        $this->network = $enabled;

        return $this;
    }

    public function optionName(string $name): static
    {
        $this->option_name = $name;

        return $this;
    }

    /**
     * Set a custom setting for the settings page.
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
     * Get the custom settings for the settings page.
     *
     * @return array<string, mixed> The custom settings.
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $pageData = EmptyValueFilter::filter(get_object_vars($this));

        // Remove the settings array and the builder-only icon keys from the page data
        unset($pageData['settings'], $pageData['icon_type'], $pageData['icon'], $pageData['icon_svg']);

        $iconUrl = $this->resolveIconUrl();
        if ($iconUrl !== null) {
            $pageData['icon_url'] = $iconUrl;
        }

        // Merge custom settings if they exist
        if (! empty($this->settings)) {
            $pageData = array_merge($pageData, $this->settings);
        }

        return $pageData;
    }

    /**
     * Resolve the menu icon into the icon_url setting read by MB Settings Page.
     *
     * The icon_type / icon / icon_svg keys only exist in Meta Box Builder, which
     * converts them before registering the page: do the same conversion here.
     */
    protected function resolveIconUrl(): ?string
    {
        if ($this->icon_url !== null) {
            return $this->icon_url;
        }

        $type = $this->icon_type ?? IconType::DASHICONS->value;

        if ($type === IconType::SVG->value && $this->icon_svg !== null) {
            return str_starts_with($this->icon_svg, 'data:')
                ? $this->icon_svg
                : 'data:image/svg+xml;base64,'.base64_encode($this->icon_svg);
        }

        if ($this->icon === null) {
            return null;
        }

        if ($type === IconType::DASHICONS->value && ! str_starts_with($this->icon, 'dashicons-')) {
            return 'dashicons-'.$this->icon;
        }

        return $this->icon;
    }
}
