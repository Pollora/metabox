<?php

declare(strict_types=1);

namespace Pollora\Metabox;

use InvalidArgumentException;
use Pollora\Metabox\Enums\BlockMode;
use Pollora\Metabox\Services\BlockTypeFilter;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * Class for creating Gutenberg blocks.
 */
class Block extends FieldGroup
{
    /**
     * The block type.
     */
    protected string $type = 'block';

    /**
     * The context of the block (override parent default).
     */
    protected string $context = 'normal';

    /**
     * The block title.
     */
    protected string $title = '';

    /**
     * The block ID.
     */
    protected string $id = '';

    /**
     * The block version.
     */
    protected string $version;

    /**
     * The icon of the block.
     */
    protected string|array $icon = 'awards';

    /**
     * The category of the block.
     */
    protected string $category = 'design';

    /**
     * The keywords for the block.
     */
    protected array $keywords = [];

    /**
     * The render template path for the block.
     */
    protected string $render_template;

    /**
     * The enqueue style URL for the block.
     */
    protected string $enqueue_style;

    /**
     * The enqueue script URL for the block.
     */
    protected string $enqueue_script;

    /**
     * The custom attributes of the block type.
     */
    protected array $attributes;

    /**
     * The supports options for the block.
     */
    protected array $supports;

    /**
     * The default mode of the block.
     */
    protected string $mode = 'edit';

    /**
     * The render callback for the block.
     */
    protected $render_callback;

    /**
     * The enqueue assets callback for the block.
     */
    protected $enqueue_assets;

    /**
     * The preview data for the block.
     */
    protected array $preview = [];

    /**
     * The post types for the block.
     */
    protected array $post_types;

    /**
     * The excluded post types for the block.
     */
    protected array $excluded_post_types;

    /**
     * Set the block version.
     *
     * @param  string  $version  The block version.
     */
    public function version(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Set the icon of the block.
     *
     * @param  string|array  $icon  The icon of the block.
     */
    public function icon(string|array $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the category of the block.
     *
     * @param  string  $category  The category of the block.
     */
    public function category(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Set the keywords for the block.
     *
     * @param  array  $keywords  The keywords for the block.
     */
    public function keywords(array $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Set the render template path for the block.
     *
     * @param  string  $renderTemplate  The render template path.
     */
    public function renderTemplate(string $renderTemplate): static
    {
        $this->render_template = $renderTemplate;

        return $this;
    }

    /**
     * Set the enqueue style URL for the block.
     *
     * @param  string  $enqueueStyle  The enqueue style URL.
     */
    public function enqueueStyle(string $enqueueStyle): static
    {
        $this->enqueue_style = $enqueueStyle;

        return $this;
    }

    /**
     * Set the enqueue script URL for the block.
     *
     * @param  string  $enqueueScript  The enqueue script URL.
     */
    public function enqueueScript(string $enqueueScript): static
    {
        $this->enqueue_script = $enqueueScript;

        return $this;
    }

    /**
     * Set the supports options for the block.
     *
     * @param  array  $supports  The supports options for the block.
     */
    public function supports(array $supports): static
    {
        $this->supports = $supports;

        return $this;
    }

    /**
     * Set the default mode of the block: edit fields or rendered preview.
     */
    public function mode(string|BlockMode $mode): static
    {
        $this->mode = OptionValidation::check($mode, BlockMode::class);

        return $this;
    }

    /**
     * Set the render callback for the block.
     *
     * @param  callable  $renderCallback  The render callback.
     */
    public function renderCallback(callable $renderCallback): static
    {
        $this->render_callback = $renderCallback;

        return $this;
    }

    /**
     * Set the enqueue assets callback for the block.
     *
     * @param  callable  $enqueueAssets  The enqueue assets callback.
     */
    public function enqueueAssets(callable $enqueueAssets): static
    {
        $this->enqueue_assets = $enqueueAssets;

        return $this;
    }

    /**
     * Set the preview data for the block.
     *
     * @param  array  $preview  The preview data.
     */
    public function preview(array $preview): static
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * Restrict block to specific post types
     *
     * @param  array  $postTypes  The allowed post types
     */
    public function restrictToPostTypes(array $postTypes): static
    {
        $this->post_types = $postTypes;
        BlockTypeFilter::registerRestrictions($this->id, ['allowed' => $postTypes]);

        return $this;
    }

    /**
     * Exclude block from specific post types
     *
     * @param  array  $postTypes  The excluded post types
     */
    public function excludePostTypes(array $postTypes): static
    {
        $this->excluded_post_types = $postTypes;
        BlockTypeFilter::registerRestrictions($this->id, ['excluded' => $postTypes]);

        return $this;
    }

    /**
     * Add attributes to the block type, e.g. ['theme' => ['type' => 'string', 'default' => 'light']].
     * They are available in the render template or callback with the field values.
     *
     * @param  array  $attributes  Attribute definitions keyed by name, each with a 'type' and an optional 'default'.
     */
    public function attributes(array $attributes): static
    {
        $types = ['string', 'number', 'integer', 'boolean', 'object', 'array', 'null'];

        foreach ($attributes as $name => $attribute) {
            if (in_array($name, ['id', 'name', 'data', 'anchor'], true)) {
                throw new InvalidArgumentException("The block attribute '{$name}' is reserved by MB Blocks.");
            }

            if (! is_array($attribute) || ! in_array($attribute['type'] ?? null, $types, true)) {
                throw new InvalidArgumentException("The block attribute '{$name}' needs a type: '".implode("', '", $types)."'.");
            }
        }

        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Build the block and return its settings.
     *
     * MB Blocks replaces its default attributes with the custom ones: keep them.
     */
    public function build(): array
    {
        $block = parent::build();

        if (isset($block['attributes'])) {
            $block['attributes'] = [
                'id' => ['type' => 'string', 'default' => $this->id],
                'name' => ['type' => 'string', 'default' => $this->id],
                'data' => ['type' => 'object', 'default' => $this->preview],
                'anchor' => ['type' => 'string', 'default' => ''],
            ] + $block['attributes'];
        }

        return $block;
    }
}
