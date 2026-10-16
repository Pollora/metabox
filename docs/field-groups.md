# Field groups

A field group is a set of fields registered together with Meta Box, the same name MB Builder and ACF use. On a post edit screen, it is displayed as a meta box; on a term, a user, a settings page or in the Customizer, as a section. The `FieldGroup` class creates field groups: this guide walks you through creating one and adding fields to it.

## Basic Usage

To create a field group, you need to use the `FieldGroup::make` method, which initializes a new field group with a title and an ID. You can then chain methods to configure the field group's properties and add fields.

### Example

```php
<?php

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Number;
use Pollora\Metabox\Fields\Text;

FieldGroup::make('User Information', 'user_info')
    ->description('A field group for user information')
    ->context('normal')
    ->priority('high')
    ->fields([
        Text::make('Username', 'username')
            ->placeholder('Enter your username'),
        Number::make('Age', 'age')
            ->min(18)
            ->max(100)
            ->step(1),
    ]);
```

### Methods

**Display**

- **`description(string $description)`**: Sets the description of the field group.
- **`context(string|Context $context)`**: Sets where the field group appears: `normal`, `advanced`, `side`, `form_top`, `after_title`, `after_editor` or `before_permalink`.
- **`priority(string|int|Priority $priority)`**: Sets the priority of the field group: `high` or `low`. An integer sets the position of a Customizer section: it throws a `LogicException` unless the field group uses `customizer()` or a settings page location.
- **`style(string|BoxStyle $style)`**: Sets the style: `default` or `seamless` (without the box wrapper).
- **`closed(bool $closed)`**: Collapses the field group by default.
- **`defaultHidden(bool $defaultHidden)`**: Hides the field group by default. It can be shown again from the screen options.
- **`class(?string $class)`**: Adds a CSS class to the field group.
- **`location(Location $location)`**: Sets where the field group is displayed. See [Setting the Location](#setting-the-location).
- **`customizer(?string $panel = null, ?string $optionName = null)`**: Displays the field group as a section of the Customizer instead of an edit screen, at the top level or in the given panel. The values are saved in the theme mods, or in the given option. See [Settings pages](settings-pages.md#customizer-sections-without-a-settings-page).

**Fields and tabs**

- **`fields(array $fields)`**: Adds fields to the field group. Accepts an array of field instances, including `Tab` and [`Column`](layout-fields.md#column) instances.
- **`geolocation(?string $apiKey = null, array $types = [], string|array $countries = [])`**: Fills the fields from an autocomplete address field (MB Geolocation). See [Extensions](extensions.md#geolocation).
- **`tabStyle(string|TabStyle $style)`**: Sets the style of the tabs: `default`, `box` or `left` (Meta Box Tabs).
- **`tabWrapper(bool $wrapper = true)`**: Set to `false` to remove the field group wrapper around the tabs.
- **`tabDefaultActive(string $tabId)`**: Sets the tab active by default.
- **`tabRemember(bool $remember = true)`**: Remembers the last active tab when saving.

**Saving and validation**

- **`validation(array $rules, array $messages = [])`**: Sets [validation rules](https://docs.metabox.io/validation/) and their error messages, keyed by input name. The input name is usually the field ID, but it is `my_field[]` for a checkbox list and `_file_my_field[]` for file and image fields.
- **`autosave(bool $autosave)`**: Saves the field values when WordPress autosaves the post.
- **`mediaModal(bool $mediaModal)`**: Shows the fields in the media modal (attachments only).
- **`revision(bool $revision = true)`**: Tracks the field values in post revisions (MB Revision).
- **`customTable(string $table, bool $prefix = true)`**: Stores the field values in a custom table (MB Custom Table), named without the WordPress table prefix, which is added. See [Custom tables](custom-tables.md).
- **`storageType(string $storageType)`**: Sets the storage type directly. Prefer `customTable()` for custom tables.

**Anything else**

- **`setting(string $key, mixed $value)`**: Adds a custom Meta Box setting that isn't explicitly defined. This allows you to pass any Meta Box-specific option directly.

### Validation example

```php
<?php

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Email;

FieldGroup::make('Contact', 'contact')
    ->fields([
        Email::make('Email', 'email'),
    ])
    ->validation(
        ['email' => ['required' => true, 'minlength' => 7]],
        ['email' => ['required' => 'Email is required']],
    );
```

## Adding Fields

Fields are added to a field group using the `fields` method. The package supports a variety of field types, including text, number, textarea, and more. Each field type has its own set of methods for configuration.

### Field Types

- **Text**: A simple text input field.
- **Number**: A numeric input field with options for min, max, and step values.
- **Textarea**: A multi-line text input field.
- **SingleImage**: An image upload field.

### Example of Adding Fields

```php
<?php

use Pollora\Metabox\Fields\Textarea;
use Pollora\Metabox\Fields\SingleImage;

FieldGroup::make('Post Details', 'post_details')
    ->fields([
        Textarea::make('Description', 'description')
            ->rows(5)
            ->placeholder('Enter a description'),
        SingleImage::make('Featured Image', 'featured_image')
            ->maxFileSize('2mb')
            ->imageSize('large'),
    ]);
```

## Custom Settings

The `setting()` method allows you to add any Meta Box-specific option that isn't wrapped by a dedicated method. This is useful when you need to use Meta Box features that aren't yet wrapped by Metabox.

### Example with Custom Settings

```php
<?php

FieldGroup::make('Advanced field group', 'advanced_field_group')
    ->setting('autosave', true)
    ->setting('validation', [
        'rules' => [
            'field_id' => [
                'required' => true,
                'minlength' => 5
            ]
        ]
    ])
    ->setting('class', 'custom-field-group-class')
    ->fields([
        Text::make('Custom Field', 'custom_field')
            ->setting('custom_attribute', 'custom_value'),
    ]);
```

Note that custom settings also work on individual fields:

```php
Text::make('Email', 'email')
    ->setting('pattern', '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$')
    ->setting('custom_validation', true);
```

## Setting the Location

The `Location` class allows you to define where the field group should appear based on specific conditions. You can use the `Location::where` method to specify these conditions.

### Example

```php
<?php

use Pollora\Metabox\Location;

FieldGroup::make('Custom field group', 'custom_field_group')
    ->location(Location::postTypes('page'))
    ->fields([
        Text::make('Page Title', 'page_title'),
    ]);
```

### Methods

- **`Location::postTypes(string|array $postTypes)`**: Shows the field group on the given post types.
- **`Location::taxonomies(string|array $taxonomies)`**: Shows the field group on terms of the given taxonomies (MB Term Meta).
- **`Location::settingsPages(string|array $settingsPages, ?string $tab = null)`**: Shows the field group on the given settings pages (MB Settings Page), in the given tab when the pages have tabs: `Location::settingsPages('theme-options', tab: 'general')`.
- **`Location::models(string|array $models)`**: Shows the field group on the screens of custom models, and stores its values in their table (MB Custom Table). See [Custom tables](custom-tables.md#custom-models).
- **`Location::user()`**: Shows the field group on user profiles (MB User Meta).
- **`Location::comment()`**: Shows the field group on comments (MB Comment Meta).
- **`andWhere(string $type, string|array $values)`**: Adds another condition with any Meta Box key.
- **`Location::where(string $type, string|array $values)`**: Creates a condition with any Meta Box key. Note that Meta Box expects `post_types`, not `post_type`.
- **`Location::default()`**: The location used when none is set: posts.

---

**Previous:** [Overview](../README.md)  
**Next:** [Common field settings](common-settings.md)
