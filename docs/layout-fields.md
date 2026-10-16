# Layout fields

The package provides a variety of fields specifically designed for layout purposes, allowing you to organize and structure your field groups and blocks in WordPress. This document covers the layout-specific fields available in the package and how to use them.

## Divider Field

The `Divider` field is used to create visual separators in the UI. It helps in organizing fields into sections.

### Example

```php
<?php

use Pollora\Metabox\Fields\Divider;

Divider::make();
```

### Methods

- This field does not have additional methods beyond those inherited from the `Field` class.

## Group Field

The `Group` field is used to create a group of fields, allowing for nested field structures.

### Example

```php
<?php

use Pollora\Metabox\Fields\Group;
use Pollora\Metabox\Fields\Text;

Group::make('Group Name', 'group_name')
    ->fields([
        Text::make('Text Field', 'text_field'),
        Divider::make(),
    ]);
```

### Methods

- **`fields(array $fields)`**: Adds fields to the group, which can include nested groups.
- **`collapsible(bool $collapsible = true)`**: Makes the group collapsible.
- **`defaultState(string|GroupState $state)`**: Sets whether a collapsible group starts `collapsed` or `expanded`.
- **`saveState(bool $saveState = true)`**: Remembers whether the group is collapsed.
- **`groupTitle(string $title)`**: Sets the title of a collapsible group. It can include field values, e.g. `{title}`, and `{#}` for the clone number.

## Heading Field

The `Heading` field is used to create section headings in the UI. It can include an optional description.

### Example

```php
<?php

use Pollora\Metabox\Fields\Heading;

Heading::make('Section Title')
    ->description('This is a section description.');
```

### Methods

- **`description(string $desc)`**: Sets the description for the heading.

## Tab Field

The `Tab` field is used to create tabbed sections in the UI, allowing for better organization of fields.

### Example

```php
<?php

use Pollora\Metabox\Fields\Tab;
use Pollora\Metabox\Fields\Text;

Tab::make('Tab Title')
    ->icon('dashicons-admin-generic')
    ->fields([
        Text::make('Text Field', 'text_field'),
    ]);
```

### Methods

- **`icon(string $icon)`**: Sets the icon for the tab.
- **`fields(array $fields)`**: Adds fields to the tab, which can include nested groups and columns.

## Column

A `Column` holds several fields in one column of the 12-column grid, one below the other. It requires [Meta Box Columns](https://docs.metabox.io/extensions/meta-box-columns/). To give a single field its own column, use [`columns()`](extensions.md#columns) on the field instead.

### Example

```php
<?php

use Pollora\Metabox\Fields\Column;

FieldGroup::make('Contact', 'contact')
    ->fields([
        Column::make(4)->fields([
            Text::make('Name', 'name'),
            Email::make('Email', 'email'),
        ]),
        Column::make(8)->class('contact-message')->fields([
            Textarea::make('Message', 'message'),
        ]),
    ]);
```

### Methods

- **`Column::make(int $size)`**: Creates a column spanning `$size` grid columns, from 1 to 12. Another size throws an exception.
- **`class(string $class)`**: Adds a CSS class to the column.
- **`fields(array $fields)`**: Adds fields, headings and dividers to the column.

Columns can be used in field groups, blocks and tabs. A tab or a column inside a column, or a column inside a group, throws an exception: inside a group, use `columns()` on the sub-fields.

---

**Previous:** [Upload fields](upload-fields.md)  
**Next:** [Blocks](blocks.md)
