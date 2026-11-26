<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields;

use Pollora\Metabox\Enums\ToolbarPosition;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * Block editor field class for editing content with the WordPress block editor.
 */
class BlockEditor extends Field
{
    /**
     * The type of field.
     */
    protected string $type = 'block_editor';

    /**
     * The block types allowed in the editor. Empty to allow all blocks.
     *
     * @var array<mixed>
     */
    protected array $allowed_blocks;

    /**
     * The height of the editor, as a CSS value.
     */
    protected string $height;

    /**
     * The position of the toolbar.
     */
    protected string $toolbar_position;

    /**
     * Set the block types allowed in the editor.
     *
     * @param  array<mixed>  $allowedBlocks  Block names, e.g. ['core/heading', 'core/image'].
     */
    public function allowedBlocks(array $allowedBlocks): static
    {
        $this->allowed_blocks = $allowedBlocks;

        return $this;
    }

    /**
     * Set the height of the editor.
     *
     * @param  string  $height  Any CSS value, e.g. '500px'. Defaults to 300px.
     */
    public function height(string $height): static
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Set the position of the toolbar.
     */
    public function toolbarPosition(string|ToolbarPosition $position): static
    {
        $this->toolbar_position = OptionValidation::check($position, ToolbarPosition::class);

        return $this;
    }
}
