# Settings pages

You can create settings pages in WordPress using the `SettingsPage` class. This functionality relies on the [MB Settings Page](https://docs.metabox.io/extensions/mb-settings-page/) extension of the Meta Box plugin.

## Basic Usage

To create a settings page, use the `SettingsPage::make()` method which initializes a new page with a page title and an ID. By default, the menu title will be the same as the page title, but you can customize it using the `menuTitle()` method.

### Example

```php
<?php

use Pollora\Metabox\SettingsPage;
use Pollora\Metabox\Enums\IconType;
use Pollora\Metabox\Enums\TabStyle;

SettingsPage::make('Theme Options', 'theme-options')
    ->menuTitle('Theme Settings')
    ->position(25)
    ->iconType('dashicons')
    ->icon('dashicons-admin-settings')
    ->capability('manage_options')
    ->tab('general', 'General Settings')
    ->tab('style', 'Style', icon: 'dashicons-art')
    ->tab('advanced', 'Advanced')
    ->tabStyle('left');
```

### Available Methods

#### Menu Configuration

- **`menuTitle(string $title)`**: Sets a custom menu title (different from page title).
- **`position(int $position)`**: Sets the menu position in the sidebar.
- **`submenuTitle(string $title)`**: Sets the default first submenu title.
- **`parent(string $parent)`**: Sets the parent menu slug (for submenus).

#### Icon Configuration

- **`iconType(IconType|string $type)`**: Sets the icon type (`DASHICONS`/`'dashicons'`, `FONTAWESOME`/`'fontawesome'`, `SVG`/`'svg'`, `URL`/`'url'`).
- **`icon(string $icon)`**: Sets the icon (for Dashicons or Font Awesome).
- **`iconSvg(string $svg)`**: Sets the custom SVG icon.
- **`iconUrl(string $url)`**: Sets the custom icon URL.

#### Display Configuration

- **`capability(string $capability)`**: Sets the required capability to access the page.
- **`class(string $class)`**: Adds a custom CSS class.
- **`style(string|SettingsPageStyle $style)`**: Sets the page style: `boxes` (default, each field group is a box) or `no-boxes` (each field group is a section, like the WordPress settings pages).
- **`columns(int $columns)`**: Sets the number of columns: `1` (default) or `2`. See [Defaults that differ from Meta Box](#defaults-that-differ-from-meta-box).

#### Tabs Configuration

- **`tab(string $id, string $label, ?string $icon = null)`**: Adds a tab. The icon is a Dashicons or icon font class, e.g. `dashicons-admin-generic`, or an image URL. Adding a tab ID twice throws an exception.
- **`tabs(array $tabs)`**: Sets all the tabs at once, as `'id' => 'Label'` or `'id' => ['label' => 'Label', 'icon' => '...']`.
- **`tabStyle(TabStyle|string $style)`**: Sets the tab style (`DEFAULT`/`'default'` or `LEFT`/`'left'`).

#### Message Configuration

- **`submitButton(string $text)`**: Customizes the submit button text.
- **`message(string $message)`**: Customizes the message displayed when saving.
- **`helpTabs(array $tabs)`**: Adds tabs to the Help panel of the page, each an array with a `title` and a `content` or a `callback`.

#### Customizer Configuration

- **`customizer(bool $enabled)`**: Enables display in the Customizer.
- **`customizerOnly(bool $enabled)`**: Shows only in the Customizer.

#### Network Configuration

- **`network(bool $enabled)`**: Enables network-wide settings (multisite).
- **`optionName(string $name)`**: Sets the option name for storing settings.

#### Custom Settings

- **`setting(string $key, mixed $value)`**: Adds a custom Meta Box setting that isn't explicitly defined. This allows you to pass any Meta Box-specific option directly.

## Usage Examples

### Simple Settings Page

```php
// Using enums
SettingsPage::make('Site Options', 'site-options')
    ->iconType(IconType::DASHICONS)
    ->icon('dashicons-admin-settings')
    ->capability('manage_options');

// Using strings
SettingsPage::make('Site Options', 'site-options')
    ->iconType('dashicons')
    ->icon('dashicons-admin-settings')
    ->capability('manage_options');
```

### Submenu Settings Page

```php
SettingsPage::make('Theme Options', 'theme-options')
    ->menuTitle('Theme Settings')
    ->parent('themes.php')
    ->capability('edit_theme_options');
```

### Settings Page with Tabs

```php
SettingsPage::make('Advanced Options', 'advanced-options')
    ->iconType(IconType::DASHICONS)
    ->icon('dashicons-admin-tools')
    ->tab('general', 'General', icon: 'dashicons-admin-settings')
    ->tab('appearance', 'Appearance', icon: 'dashicons-admin-customizer')
    ->tabStyle(TabStyle::LEFT)
    ->columns(2);

FieldGroup::make('Colors', 'colors')
    ->location(Location::settingsPages('advanced-options', tab: 'appearance'))
    ->fields([
        Color::make('Text color', 'text_color'),
    ]);
```

### Backup and restore

A `Backup` field shows the values of the settings page as JSON. Copy them to back up the settings; paste a backup and save the page to restore them. It only works in a field group displayed on a settings page.

```php
use Pollora\Metabox\Fields\Backup;

FieldGroup::make('Backup', 'backup')
    ->location(Location::settingsPages('advanced-options', tab: 'advanced'))
    ->fields([
        Backup::make(),
    ]);
```

- **`Backup::make(string $name = 'Backup', string $id = 'backup')`**: Creates the field. Its value is not saved.
- **`rows(int $rows)`**: Sets the number of rows of the textarea. Defaults to 5.

### Customizer Settings Page

```php
SettingsPage::make('Customizer Options', 'customizer-options')
    ->customizer(true)
    ->optionName('my_theme_options');
```

Each field group of the page becomes a section of the Customizer. An integer `priority()` on the field group sets the position of the section.

### Customizer sections without a settings page

A field group can be a Customizer section on its own, without a settings page: its values are saved in the theme mods of the active theme, or in the option given with `optionName`.

```php
FieldGroup::make('Header', 'header')
    ->customizer()               // a top-level section
    ->priority(30)
    ->fields([
        FileInput::make('Logo', 'logo'),
    ]);

FieldGroup::make('Footer', 'footer')
    ->customizer(panel: 'theme_options', optionName: 'footer_settings'); // in the panel 'theme_options'
```

`File` and `Image` fields are converted to `FileAdvanced` and `ImageAdvanced` by MB Settings Page, as the Customizer does not support them.

## Defaults that differ from Meta Box

A settings page has **one column** by default: the field groups take the full width and the save button is below them. MB Settings Page defaults to two columns, with a sidebar holding the save button.

The package keeps one column on purpose: changing it would change the layout of the existing settings pages. To get the Meta Box layout, call `columns(2)`:

```php
SettingsPage::make('Theme Options', 'theme-options')
    ->columns(2);
```

With two columns, a field group registered with `->context('side')` is displayed in the sidebar, under the save button.

## Custom Settings

The `setting()` method allows you to add any Meta Box settings page option that isn't wrapped by a dedicated method. This is particularly useful for advanced configurations.

### Example with Custom Settings

```php
<?php

SettingsPage::make('Advanced Settings', 'advanced-settings')
    ->parent('options-general.php')
    ->setting('help_tabs', [
        [
            'title' => 'Overview',
            'content' => '<p>This is the overview tab content.</p>'
        ],
        [
            'title' => 'FAQ',
            'content' => '<p>Frequently asked questions...</p>'
        ]
    ])
    ->setting('contextual_help', 'This is contextual help content')
    ->setting('ajax', true)
    ->setting('sanitize_callback', 'custom_sanitize_function')
    ->tabs([
        'general' => 'General',
        'advanced' => 'Advanced'
    ]);
```

### Use Cases for Custom Settings

Custom settings are useful when you need to:
- Add help tabs to your settings page
- Set up custom sanitization callbacks
- Enable AJAX functionality
- Add any Meta Box Settings Page feature not yet wrapped by a dedicated method

### Important Note

When using custom settings, refer to the [Meta Box Settings Page documentation](https://docs.metabox.io/extensions/mb-settings-page/) for available options and their correct format.

---

**Previous:** [Blocks](blocks.md)  
**Next:** [Extensions](extensions.md)
