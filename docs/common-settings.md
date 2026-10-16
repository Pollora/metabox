# Common field settings

Every field extending `Field` shares the following settings, on top of its own methods.

### Example

```php
<?php

use Pollora\Metabox\Fields\Text;

Text::make('Product code', 'product_code')
    ->description('6 uppercase letters or digits')
    ->required()
    ->attributes(['maxlength' => 6, 'pattern' => '[A-Z0-9]+'])
    ->class('product-code')
    ->sanitizeCallback('sanitize_text_field')
    ->hideFromRest();
```

### Methods

**Labels and values**

- **`description(string $description)`**: Sets the description displayed below the field (`desc`).
- **`labelDescription(string $labelDescription)`**: Sets the description displayed below the label.
- **`placeholder(string|array $placeholder)`**: Sets the placeholder.
- **`default(mixed $value)`**: Sets the default value (`std`).

**Input behavior**

- **`required(bool $required = true)`**: Makes the field required.
- **`readOnly(bool $readOnly = true)`**: Makes the field read-only.
- **`disabled(bool $disabled = true)`**: Disables the field.
- **`autofocus(bool $autofocus = true)`**: Focuses the field input when the page loads.
- **`fieldName(string $fieldName)`**: Sets the name of the input, when it must differ from the field ID.
- **`multiple(bool $multiple = true)`**: Allows multiple values.
- **`attributes(array $attributes)`**: Sets custom HTML attributes, e.g. `maxlength`, `pattern`, `min` or `data-*`. Array values are JSON-encoded.
- **`attribute(string $key, mixed $value)`**: Sets a single custom HTML attribute.

**Cloning**

- **`cloneable(bool $clone = true)`**: Makes the field cloneable.
- **`sortable(bool $sortClone = true)`**: Allows reordering clones (`sort_clone`).
- **`cloneDefaults(bool $cloneDefault = true)`**: Clones the default value.
- **`cloneAsMultiple(bool $cloneAsMultiple = true)`**: Saves clones as multiple meta rows.
- **`minClone(int $minClone)`** / **`maxClone(int|string $maxClone)`**: Sets the number of clones allowed.
- **`addButton(string $addButton)`**: Sets the text of the "add clone" button.
- **`cloneEmptyStart(bool $cloneEmptyStart = true)`**: Starts without any clone: only the add button is displayed.

**Layout**

- **`tab(string $tab)`**: Places the field in a tab. Set automatically when the field is inside a `Tab`.
- **`class(string $class)`**: Adds a CSS class to the field wrapper.
- **`before(string $html)`** / **`after(string $html)`**: Outputs custom HTML before or after the field.

**Saving and visibility**

- **`saveField(bool $saveField = true)`**: Set to `false` when the value is saved by custom code.
- **`sanitizeCallback(callable|string $callback)`**: Sets a custom sanitize callback, or `'none'` to skip sanitization.
- **`hideFromRest(bool $hide = true)`**: Hides the field from the REST API.
- **`hideFromFront(bool $hide = true)`**: Hides the field from front-end forms (MB Frontend Submission).
- **`hideFromBlockBindings(bool $hide = true)`**: Hides the field from the block bindings of the block editor (WordPress 6.5+).

**Anything else**

- **`setting(string $key, mixed $value)`**: Sets any other Meta Box setting.

Conditional logic, columns, tooltips, admin columns, text limits, geolocation and SEO analysis have their own methods: see [Extensions](extensions.md).

---

**Previous:** [Field groups](field-groups.md)  
**Next:** [Field list](field-list.md)
