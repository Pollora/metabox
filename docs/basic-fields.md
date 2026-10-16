# Basic fields

The package provides a variety of basic fields that can be used to create custom field groups and blocks in WordPress. This document covers the basic fields available in the package and how to use them.

## Text Field

The `Text` field is used to create a simple text input field.

### Example

```php
<?php

use Pollora\Metabox\Fields\Text;

Text::make('Username', 'username')
    ->placeholder('Enter your username')
    ->size(30)
    ->prepend('@')
    ->append('.com');
```

### Methods

- **`type(string|InputTextType $type)`**: Sets the input type: `text` (default), `url`, `email`, `search`, `tel`, `month`, `week` or `datetime-local`.
- **`maxCharacters(int $limit)`** / **`maxWords(int $limit)`**: Limits the length with a live counter (MB Text Limiter). See [Extensions](extensions.md#text-limiter).
- The [input settings](#input-settings) below.

### Input settings

`Text`, `Email`, `Url`, `Number`, `Range` and `Password` are input fields, and share these settings:

- **`placeholder(string $text)`**: Sets the placeholder text.
- **`size(int $size)`**: Sets the size of the input.
- **`prepend(string $text)`** / **`append(string $text)`**: Displays a text before or after the input.
- **`datalist(string $id, array $options)`**: Suggests values while typing.
- **`autocomplete(string $autocomplete)`**: Sets the browser autocomplete attribute, e.g. `email`, `tel` or `off`.
- **`minLength(int $length)`** / **`maxLength(int $length)`**: Sets the minimum or maximum number of characters, checked by the browser.
- **`pattern(string $pattern)`**: Sets a regular expression the value must match, checked by the browser, e.g. `[0-9]{5}`.

## Textarea Field

The `Textarea` field is used to create a multi-line text input field.

### Example

```php
<?php

use Pollora\Metabox\Fields\Textarea;

Textarea::make('Description', 'description')
    ->rows(5)
    ->cols(60);
```

### Methods

- **`rows(int $rows)`**: Sets the number of rows for the textarea. Defaults to 4 (3 in Meta Box).
- **`cols(int $cols)`**: Sets the number of columns for the textarea. Defaults to 60.
- **`wrap(string|TextareaWrap $wrap)`**: Sets how the text wraps when the form is submitted: `soft` (default), `hard` (line breaks are added at the `cols` width) or `off`. Any other value throws an exception.
- **`autocomplete(string $autocomplete)`**, **`minLength(int $length)`** and **`maxLength(int $length)`**: Same as the [input settings](#input-settings).

## Select Field

The `Select` field is used to create a dropdown select field.

### Example

```php
<?php

use Pollora\Metabox\Fields\Select;

Select::make('Country', 'country')
    ->options([
        'us' => 'United States',
        'ca' => 'Canada',
        'uk' => 'United Kingdom',
    ])
    ->flatten();
```

### Methods

- **`options(array $options)`**: Sets the options for the select field.
- **`flatten(bool $flatten = true)`**: Sets whether to display sub-items without indentation.

## SelectTree Field

The `SelectTree` field displays hierarchical options as a select per level: selecting an option shows the select of its children. It saves several values: the selected option and its parents.

### Example

```php
<?php

use Pollora\Metabox\Fields\SelectTree;

SelectTree::make('Region', 'region')
    ->placeholder('Select a region')
    ->tree([
        'europe' => [
            'label' => 'Europe',
            'children' => [
                'fr' => 'France',
                'be' => ['label' => 'Belgium', 'children' => ['brussels' => 'Brussels']],
            ],
        ],
        'asia' => 'Asia',
    ]);
```

### Methods

- **`tree(array $tree)`**: Sets the options. Each option is a `'value' => 'Label'` pair, or `'value' => ['label' => 'Label', 'children' => [...]]` for an option with children. A malformed option, or a value used twice, throws an exception.

## Checkbox Field

The `Checkbox` field is used to create a checkbox input field.

### Example

```php
<?php

use Pollora\Metabox\Fields\Checkbox;

Checkbox::make('Subscribe', 'subscribe')
    ->checkedByDefault();
```

### Methods

- **`checkedByDefault(bool $std = true)`**: Sets whether the checkbox is checked by default.

## CheckboxList Field

The `CheckboxList` field is used to create a list of checkboxes.

### Example

```php
<?php

use Pollora\Metabox\Fields\CheckboxList;

CheckboxList::make('Interests', 'interests')
    ->options([
        'sports' => 'Sports',
        'music' => 'Music',
        'movies' => 'Movies',
    ])
    ->toggleAllButton();
```

### Methods

- **`options(array $options)`**: Sets the options for the checkbox list.
- **`tree(array $tree)`**: Sets hierarchical options, displayed as a checkbox tree. See [SelectTree](#selecttree-field) for the format.
- **`collapse(bool $collapse = true)`**: For a tree, hides the children of an option until it is checked. Defaults to `true`: use `collapse(false)` to show every option.
- **`toggleAllButton(bool $select_all_none = true)`**: Adds a button checking or unchecking every option.

### Checkbox tree

```php
CheckboxList::make('Regions', 'regions')
    ->tree([
        'europe' => ['label' => 'Europe', 'children' => ['fr' => 'France', 'be' => 'Belgium']],
        'asia' => 'Asia',
    ])
    ->collapse(false);
```

## Radio Field

The `Radio` field is used to create a set of radio button input fields.

### Example

```php
<?php

use Pollora\Metabox\Fields\Radio;

Radio::make('Gender', 'gender')
    ->options([
        'male' => 'Male',
        'female' => 'Female',
    ])
    ->inline();
```

### Methods

- **`options(array $options)`**: Sets the options for the radio field.
- **`inline()`**: Sets whether the radio buttons should be displayed inline.

---

**Previous:** [Field list](field-list.md)  
**Next:** [Advanced fields](advanced-fields.md)
