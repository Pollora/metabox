<?php

declare(strict_types=1);

namespace Pollora\Metabox;

use LogicException;
use Pollora\Metabox\CustomTable\TableName;
use Pollora\Metabox\Enums\ModelSupport;
use Pollora\Metabox\Exception\WordPressNotLoadedException;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * Custom model: data stored only in a custom table, managed from its own admin
 * screens, like a post type (MB Custom Table).
 *
 * @phpstan-consistent-constructor
 */
class MetaboxModel
{
    /**
     * The models declared, by name.
     *
     * @var array<string, MetaboxModel>
     */
    protected static array $models = [];

    protected ?TableName $table = null;

    protected array $labels = [];

    protected bool $show_in_menu = true;

    protected ?int $menu_position = null;

    protected ?string $menu_icon = null;

    protected ?string $parent = null;

    protected ?string $capability = null;

    /**
     * @var array<string>
     */
    protected array $supports = [];

    /**
     * @param  string  $name  The model name, used in the admin URLs and by Location::models().
     */
    public function __construct(protected string $name)
    {
        if (! function_exists('add_action')) {
            throw WordPressNotLoadedException::forMetaboxMaker();
        }

        if (isset(static::$models[$name])) {
            throw new LogicException("The model '{$name}' is already declared.");
        }

        if (did_action('init') && ! doing_action('init')) {
            throw new LogicException("The model '{$name}' is declared too late: declare it before the init action.");
        }

        static::$models[$name] = $this;

        add_action('init', function () {
            mb_register_model($this->name, $this->build());
        }, doing_action('init') ? PHP_INT_MAX : 10);
    }

    /**
     * Create a custom model. It is registered with MB Custom Table automatically.
     *
     * @param  string  $name  The model name, used in the admin URLs and by Location::models().
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * The model declared with this name, if any.
     */
    public static function get(string $name): ?self
    {
        return static::$models[$name] ?? null;
    }

    /**
     * Set the table storing the model, without the WordPress table prefix, which is added.
     *
     * @param  string  $table  The table name, or a class with a getTable() method, such as an Eloquent model.
     * @param  bool  $prefix  Set to false for an existing table without the prefix.
     */
    public function table(string $table, bool $prefix = true): static
    {
        $this->table = new TableName($table, $prefix);

        return $this;
    }

    /**
     * Set the labels of the model. The other labels default to generic ones, such as
     * "Add New Item", translated by MB Custom Table.
     */
    public function labels(
        string $plural,
        string $singular,
        ?string $menuName = null,
        ?string $allItems = null,
        ?string $addNew = null,
        ?string $addNewItem = null,
        ?string $editItem = null,
        ?string $searchItems = null,
        ?string $notFound = null,
        ?string $itemAdded = null,
        ?string $itemUpdated = null,
        ?string $itemDeleted = null,
    ): static {
        $this->labels = array_filter([
            'name' => $plural,
            'singular_name' => $singular,
            'menu_name' => $menuName,
            'all_items' => $allItems,
            'add_new' => $addNew,
            'add_new_item' => $addNewItem,
            'edit_item' => $editItem,
            'search_items' => $searchItems,
            'not_found' => $notFound,
            'item_added' => $itemAdded,
            'item_updated' => $itemUpdated,
            'item_deleted' => $itemDeleted,
        ], fn ($label) => $label !== null);

        return $this;
    }

    /**
     * Set the menu icon: a Dashicons class, e.g. 'dashicons-money-alt', an image URL or a base64-encoded SVG data URI.
     */
    public function menuIcon(string $icon): static
    {
        $this->menu_icon = $icon;

        return $this;
    }

    /**
     * Set the position of the menu in the admin menu.
     */
    public function menuPosition(int $position): static
    {
        $this->menu_position = $position;

        return $this;
    }

    /**
     * Show the menu as a submenu of the given menu, e.g. 'tools.php'.
     */
    public function parent(string $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Set whether the model has a menu in the admin menu. Defaults to true.
     */
    public function showInMenu(bool $show = true): static
    {
        $this->show_in_menu = $show;

        return $this;
    }

    /**
     * Set the capability required to list, create, edit and delete the items. Defaults to 'edit_posts'.
     */
    public function capability(string $capability): static
    {
        $this->capability = $capability;

        return $this;
    }

    /**
     * Fill data automatically: the author, the published date or the modified date of the items.
     * MB Custom Table adds their columns to the table it creates.
     */
    public function supports(string|ModelSupport ...$supports): static
    {
        $this->supports = array_values(array_unique(array_map(
            fn ($support) => OptionValidation::check($support, ModelSupport::class),
            $supports,
        )));

        return $this;
    }

    /**
     * The full name of the table storing the model.
     */
    public function getTable(): string
    {
        if ($this->table === null) {
            throw new LogicException("The model '{$this->name}' needs a table: call table().");
        }

        return $this->table->resolve();
    }

    /**
     * Build the model and return its MB Custom Table settings.
     */
    public function build(): array
    {
        return array_filter([
            'table' => $this->getTable(),
            'labels' => $this->labels,
            'show_in_menu' => $this->show_in_menu ? null : false,
            'menu_position' => $this->menu_position,
            'menu_icon' => $this->menu_icon,
            'parent' => $this->parent,
            'capability' => $this->capability,
            'supports' => $this->supports,
        ], fn ($value) => $value !== null && $value !== []);
    }
}
