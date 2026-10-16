<p align="center">
  <a href="https://pollora.dev">
    <img src="https://raw.githubusercontent.com/Pollora/.github/main/brand/banners/metabox.png" width="100%" alt="Metabox: Meta Box fields, blocks and settings pages in a fluent API">
  </a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/pollora/metabox"><img src="https://img.shields.io/packagist/v/pollora/metabox" alt="Latest version"></a>
  <a href="https://packagist.org/packages/pollora/metabox"><img src="https://img.shields.io/packagist/dt/pollora/metabox" alt="Total downloads"></a>
  <a href="https://github.com/Pollora/metabox/actions/workflows/tests.yml"><img src="https://github.com/Pollora/metabox/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/Pollora/metabox" alt="License"></a>
</p>

Metabox declares [Meta Box](https://metabox.io) meta boxes, custom fields, Gutenberg blocks and settings pages with chained, type-hinted methods instead of the nested arrays passed to the `rwmb_meta_boxes` filter. Each field type is a class, each setting a method, enumerated values are checked when they are set, and the configuration is registered with Meta Box for you. It is for WordPress developers who use Meta Box and are tired of looking up array keys.

> Part of [Pollora](https://pollora.dev), the Laravel framework for WordPress. It works in any WordPress project that uses Composer, with or without Pollora.

## Installation

```bash
composer require pollora/metabox
```

Requires PHP 8.2+, WordPress and the [Meta Box](https://wordpress.org/plugins/meta-box/) plugin. Some features need a Meta Box extension: [MB Blocks](https://docs.metabox.io/extensions/mb-blocks/) for blocks, [MB Settings Page](https://docs.metabox.io/extensions/mb-settings-page/) for settings pages, [Meta Box Group](https://docs.metabox.io/extensions/meta-box-group/) for groups and [Meta Box Tabs](https://docs.metabox.io/extensions/meta-box-tabs/) for tabs. They are all included in Meta Box AIO.

Coming from `amphibee/metabox-maker`? Read the [upgrade guide](UPGRADE.md).

## Quick start

A field group with two fields in the sidebar of the page editor:

```php
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Number;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Location;

FieldGroup::make('Event', 'event')
    ->location(Location::postTypes('page'))
    ->context('side')
    ->fields([
        Text::make('Venue', 'venue')
            ->placeholder('Where does it take place?')
            ->required(),
        Number::make('Seats', 'seats')
            ->min(1)
            ->step(1),
    ]);
```

A Gutenberg block with a repeatable group of links:

```php
use Pollora\Metabox\Block;
use Pollora\Metabox\Fields\Group;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Fields\Url;

Block::make('Useful links', 'useful-links')
    ->icon('admin-links')
    ->category('widgets')
    ->renderTemplate(get_theme_file_path('blocks/useful-links.php'))
    ->fields([
        Text::make('Title', 'title'),
        Group::make('Links', 'links')
            ->cloneable()
            ->addButton('Add a link')
            ->fields([
                Text::make('Label', 'label'),
                Url::make('URL', 'url'),
            ]),
    ]);
```

A settings page, and a field group displayed on it:

```php
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Email;
use Pollora\Metabox\Location;
use Pollora\Metabox\SettingsPage;

SettingsPage::make('Site options', 'site-options')
    ->icon('admin-generic')
    ->optionName('site_options');

FieldGroup::make('Contact', 'contact')
    ->location(Location::settingsPages('site-options'))
    ->fields([
        Email::make('Contact email', 'contact_email'),
    ]);
```

Declare them when Meta Box reads its configuration, for instance in a theme's `functions.php` or on the `init` hook. Values are read with Meta Box's own functions, such as `rwmb_meta( 'venue' )`.

## Why Metabox

Compared with the arrays passed to the `rwmb_meta_boxes` filter:

- Each field type is a class and each setting a method: the IDE completes them, and a typo is an error instead of a setting Meta Box silently ignores.
- Enumerated values (contexts, styles, operators, block modes…) are checked when they are set, with a message listing the allowed values.
- Extension settings get readable methods: `visibleWhen()`, `adminColumn(after: 'title')`, `include(Rule::template(...))`.
- Fields can be referenced by instance in conditions, so renaming a field ID updates them too.
- The field group is registered for you: no filter callback to write.

### With Metabox

```php
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Number;
use Pollora\Metabox\Fields\Select;
use Pollora\Metabox\Fields\Url;
use Pollora\Metabox\Location;
use Pollora\Metabox\Rules\Rule;

$linkType = Select::make('Link type', 'link_type')
    ->options(['page' => 'Page', 'custom' => 'Custom URL']);

FieldGroup::make('Call to action', 'cta')
    ->location(Location::postTypes('page'))
    ->context('side')
    ->include(Rule::template('templates/landing.php'))
    ->fields([
        $linkType,
        Url::make('URL', 'cta_url')->visibleWhen($linkType, 'custom')->required(),
        Number::make('Discount', 'discount')->min(0)->max(100)->columns(6)->tooltip('In percent'),
    ]);
```

### The equivalent Meta Box array

```php
add_filter('rwmb_meta_boxes', function (array $metaBoxes): array {
    $metaBoxes[] = [
        'id' => 'cta',
        'title' => 'Call to action',
        'post_types' => ['page'],
        'context' => 'side',
        'include' => [
            'relation' => 'AND',
            'template' => ['templates/landing.php'],
        ],
        'fields' => [
            [
                'type' => 'select',
                'name' => 'Link type',
                'id' => 'link_type',
                'options' => ['page' => 'Page', 'custom' => 'Custom URL'],
            ],
            [
                'type' => 'url',
                'name' => 'URL',
                'id' => 'cta_url',
                'visible' => ['link_type', '=', 'custom'],
                'required' => true,
            ],
            [
                'type' => 'number',
                'name' => 'Discount',
                'id' => 'discount',
                'min' => 0,
                'max' => 100,
                'columns' => 6,
                'tooltip' => 'In percent',
            ],
        ],
    ];

    return $metaBoxes;
});
```

## Features

- About 50 field types, from text inputs to files, maps, posts, taxonomies and users, with the settings of each type as methods.
- Field groups (meta boxes on edit screens), Gutenberg blocks (MB Blocks) and settings pages (MB Settings Page), displayed on post types, terms, users, comments or settings pages.
- Groups and tabs, with nested fields.
- Enumerated settings (context, priority, styles…) accept a string or an enum, and an invalid value throws an exception.
- Blocks restricted to, or excluded from, given post types.
- Dedicated methods for the Conditional Logic, Columns, Tooltip, Admin Columns, Text Limiter, Include Exclude and Show Hide extensions, and `setting()` for any other Meta Box setting.
- Tested with Pest and analysed with PHPStan.

## Documentation

- [Field groups](docs/field-groups.md): creating field groups and choosing where they are displayed.
- [Common field settings](docs/common-settings.md): the settings shared by every field.
- [Basic fields](docs/basic-fields.md): text, textarea, select, checkbox, radio…
- [Advanced fields](docs/advanced-fields.md): autocomplete, color, date and time pickers, maps, sliders, WYSIWYG…
- [HTML5 fields](docs/html5-fields.md): email, number, range, URL.
- [WordPress fields](docs/wordpress-fields.md): post, taxonomy, user and sidebar selectors.
- [Upload fields](docs/upload-fields.md): files, images and videos.
- [Layout fields](docs/layout-fields.md): headings, dividers, groups and tabs.
- [Blocks](docs/blocks.md): Gutenberg blocks with MB Blocks.
- [Settings pages](docs/settings-pages.md): settings pages with MB Settings Page.
- [Extensions](docs/extensions.md): conditional logic, columns, tooltips, admin columns, text limits, and rules registering or displaying field groups.
- [Relationships](docs/relationships.md): relationships between posts, terms and users with MB Relationships.
- [Custom tables](docs/custom-tables.md): custom tables and custom models with MB Custom Table. In Pollora, [pollora/metabox-bridge](https://github.com/Pollora/metabox-bridge) adds migrations and Eloquent models.
- [Attributes](docs/attributes.md): declaring field groups, blocks and settings pages with attributes, discovered in Pollora by pollora/metabox-bridge.

For every setting, the reference is the [Meta Box documentation](https://docs.metabox.io).

## Testing

```bash
composer test
```

This runs the Pest tests, PHPStan and Pint. The tests check the configuration arrays generated for Meta Box, with the WordPress hook functions stubbed in `tests/Pest.php`.

## Contributing

Contributions are welcome: see the [contributing guide](https://github.com/Pollora/.github/blob/main/CONTRIBUTING.md). Report security issues privately, as described in the [security policy](https://github.com/Pollora/.github/blob/main/SECURITY.md).

## License

Metabox is open-source software licensed under the [MIT license](LICENSE). © [RuBee group](https://rubee.group)
