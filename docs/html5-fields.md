# HTML5 fields

The package provides a variety of HTML5 fields that can be used to create modern and interactive field groups and blocks in WordPress. This document covers the HTML5 fields available in the package and how to use them.

## Email Field

The `Email` field is used to create email input fields. It accepts the [input settings](basic-fields.md#input-settings).

### Example

```php
<?php

use Pollora\Metabox\Fields\Email;

Email::make('Email Address', 'email_address')
    ->size(30)
    ->prepend('user@')
    ->append('.com');
```

### Methods

- **`size(int $size)`**: Sets the size of the input field.
- **`prepend(string $text)`**: Sets the text to prepend to the input field.
- **`append(string $text)`**: Sets the text to append to the input field.

## Number Field

The `Number` field is used to create number input fields. It accepts the [input settings](basic-fields.md#input-settings), plus `min()`, `max()` and `step()`.

### Example

```php
<?php

use Pollora\Metabox\Fields\Number;

Number::make('Quantity', 'quantity')
    ->min(1)
    ->max(100)
    ->step(1);
```

### Methods

- **`min(int $min)`**: Sets the minimum value for the number field.
- **`max(int $max)`**: Sets the maximum value for the number field.
- **`step(int $step)`**: Sets the step value for the number field.

## Range Field

The `Range` field is used to create range input fields. It accepts the [input settings](basic-fields.md#input-settings), plus `min()`, `max()` and `step()`.

### Example

```php
<?php

use Pollora\Metabox\Fields\Range;

Range::make('Volume', 'volume')
    ->min(0)
    ->max(100)
    ->step(5);
```

### Methods

- **`min(int $min)`**: Sets the minimum value for the range field.
- **`max(int $max)`**: Sets the maximum value for the range field.
- **`step(int $step)`**: Sets the step value for the range field.

## Url Field

The `Url` field is used to create URL input fields. It accepts the [input settings](basic-fields.md#input-settings).

### Example

```php
<?php

use Pollora\Metabox\Fields\Url;

Url::make('Website', 'website')
    ->size(50)
    ->prepend('https://')
    ->append('.com');
```

### Methods

- **`size(int $size)`**: Sets the size of the input field.
- **`prepend(string $text)`**: Sets the text to prepend to the input field.
- **`append(string $text)`**: Sets the text to append to the input field.

---

**Previous:** [Advanced fields](advanced-fields.md)  
**Next:** [WordPress fields](wordpress-fields.md)
