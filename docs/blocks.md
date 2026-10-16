# Blocks

The `Block` class extends the `FieldGroup` class to provide a streamlined way to create custom Gutenberg blocks in WordPress. This guide will walk you through the process of creating a block and adding fields to it.

## Basic Usage

To create a block, you need to use the `Block::make` method, which initializes a new block with a title and an ID. You can then chain methods to configure the block's properties and add fields.

### Example

```php
<?php

use Pollora\Metabox\Block;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Fields\Wysiwyg;
use Pollora\Metabox\Fields\Group;

Block::make('Example Block', 'example-block')
    ->description('An example Gutenberg block')
    ->icon('book-alt')
    ->category('design')
    ->renderCallback(function ($attributes) {
        echo '<div>' . $attributes['content'] . '</div>';
    })
    ->fields([
        Text::make('Title', 'title'),
        Wysiwyg::make('Content', 'content'),
        Group::make('Links', 'links')
            ->cloneable()
            ->addButton('Add a link')
            ->maxClone(3)
            ->fields([
                Text::make('Link', 'link'),
                Text::make('Label', 'label'),
            ]),
    ]);
```

### Methods

- **`description(string $description)`**: Sets the description of the block.
- **`icon(string|array $icon)`**: Sets the icon for the block. Can be a Dashicon, FontAwesome icon, or custom SVG.
- **`category(string $category)`**: Sets the category of the block: `text`, `media`, `design` (default), `widgets`, `theme`, `embed`, or a custom category.
- **`keywords(array $keywords)`**: Sets the keywords used to search the block.
- **`version(string $version)`**: Sets the block version.
- **`context(string|Context $context)`**: Sets where the block settings are displayed: `side` (in the block sidebar) or `normal` (in the block, by clicking the edit icon). Defaults to `normal`, while MB Blocks defaults to `side`: see [Defaults that differ from Meta Box](#defaults-that-differ-from-meta-box).
- **`mode(string|BlockMode $mode)`**: Sets the default mode of the block: `edit` (default) shows the fields, `preview` shows the rendered block. Any other value throws an exception.
- **`supports(array $supports)`**: Sets the block supports, e.g. `['align' => ['wide', 'full']]`.
- **`renderTemplate(string $path)`**: Renders the block with a PHP template.
- **`renderCallback(callable $callback)`**: Renders the block with a callback.
- **`enqueueStyle(string $url)`** / **`enqueueScript(string $url)`**: Enqueues a stylesheet or a script with the block.
- **`enqueueAssets(callable $callback)`**: Enqueues assets with a callback.
- **`preview(array $preview)`**: Sets the field values used in the block inserter preview.
- **`attributes(array $attributes)`**: Adds attributes to the block type, e.g. `['theme' => ['type' => 'string', 'default' => 'light']]`, available in the render template or callback. Each attribute needs a type: `string`, `number`, `integer`, `boolean`, `object`, `array` or `null`. The MB Blocks attributes (`id`, `name`, `data` and `anchor`) are kept, and their names are reserved.
- **`storageType(string $storageType)`**: Sets where the field values are stored: `attributes` (default, in the block), `post_meta` or `custom_table`.
- **`customTable(string $table, bool $prefix = true)`**: Stores the field values in a custom table (MB Custom Table), named without the WordPress table prefix. See [Custom tables](custom-tables.md).
- **`restrictToPostTypes(array $postTypes)`** / **`excludePostTypes(array $postTypes)`**: See [Restricting Blocks by Post Type](#restricting-blocks-by-post-type).
- **`fields(array $fields)`**: Adds fields to the block. Accepts an array of field instances.
- **`setting(string $key, mixed $value)`**: Adds a custom Meta Box setting that isn't explicitly defined. This allows you to pass any Meta Box-specific option directly.

## Adding Fields

Fields are added to a block using the `fields` method. The package supports a variety of field types, including text, wysiwyg, group, and more. Each field type has its own set of methods for configuration.

### Field Types

- **Text**: A simple text input field.
- **Wysiwyg**: A rich text editor field.
- **Group**: A repeatable group of fields.
- **SingleImage**: An image upload field.

### Example of Adding Fields

```php
<?php

use Pollora\Metabox\Fields\Textarea;
use Pollora\Metabox\Fields\SingleImage;

Block::make('Post Details', 'post_details')
    ->fields([
        Textarea::make('Description', 'description')
            ->rows(5)
            ->placeholder('Enter a description'),
        SingleImage::make('Featured Image', 'featured_image')
            ->maxFileSize('2mb')
            ->imageSize('large'),
    ]);
```

## Restricting Blocks by Post Type

You can restrict the availability of blocks based on post types, making it easy to control which blocks can be used in different content types.

### Methods

- **`restrictToPostTypes(array $postTypes)`**: Restricts the block to be used only with the specified post types.
- **`excludePostTypes(array $postTypes)`**: Prevents the block from being used with the specified post types.

### Examples

#### Restricting a Block to Specific Post Types:

```php
<?php

Block::make('My Block', 'my-block')
    ->restrictToPostTypes(['page', 'product'])
    ->fields([
        // field definitions
    ]);
```

In this example, "My Block" will only be available for "page" and "product" post types.

#### Excluding a Block from Specific Post Types:

```php
<?php

Block::make('Blog Summary', 'summary-post')
    ->excludePostTypes(['post'])
    ->fields([
        // field definitions
    ]);
```

In this example, the "Blog Summary" block will be available for all post types except for "post".

### How It Works

This functionality uses the WordPress `allowed_block_types_all` filter to control which blocks are available in the editor based on the current post type.

The `BlockTypeFilter` service manages these restrictions efficiently, respecting the constraints defined for each block.

### Important Notes

- If you use both `restrictToPostTypes()` and `excludePostTypes()` on the same block, both restrictions will be applied.
- These methods only affect the visibility of blocks in the editor interface, not blocks that have already been inserted into existing content.

## Defaults that differ from Meta Box

A block displays its fields **in the block body** by default (`context` set to `normal`): the fields appear in the block when it is in edit mode, or when clicking its edit icon in preview mode. MB Blocks defaults to `side`, which displays the fields in the block sidebar of the editor.

The package keeps `normal` on purpose: changing it would move the fields of the existing blocks. To get the MB Blocks behavior, call `context('side')`:

```php
Block::make('Hero', 'hero')
    ->context('side')
    ->fields([
        Text::make('Title', 'title'),
    ]);
```

## Custom Settings

Just like with metaboxes, the `setting()` method allows you to add any Meta Box-specific option that isn't wrapped by a dedicated method. This is particularly useful for block-specific settings.

### Example with Custom Settings

```php
<?php

Block::make('Advanced Block', 'advanced-block')
    ->setting('supports', [
        'align' => ['wide', 'full'],
        'html' => false,
        'multiple' => true,
    ])
    ->setting('example', [
        'attributes' => [
            'title' => 'Example Title',
            'content' => 'Example content...'
        ]
    ])
    ->setting('keywords', ['custom', 'advanced', 'block'])
    ->fields([
        Text::make('Title', 'title')
            ->setting('save_field', false),
        Wysiwyg::make('Content', 'content')
            ->setting('autoresize', true),
    ]);
```

The custom settings work on both the block itself and individual fields within the block.

---

**Previous:** [Layout fields](layout-fields.md)  
**Next:** [Settings pages](settings-pages.md)
