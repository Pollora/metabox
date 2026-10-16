# Changelog

## 3.1.0 - 2026-10-16

### Added

- Attributes declaring field groups, blocks and settings pages: `#[FieldGroup]`, `#[Block]` and `#[SettingsPage]`, with a `fields()` method and an optional `configuring()` method receiving the builder. See [Attributes](docs/attributes.md).
- Targets of a field group: `#[PostTypes]`, `#[Taxonomies]` and `#[SettingsPages]`, accepting slugs or the classes declaring them.
- Block settings: `#[Icon]`, `#[Description]`, `#[Category]`, `#[Mode]` and `#[View]`; settings page settings: `#[Icon]`, `#[Position]`, `#[OptionName]` and `#[SubmitButton]`. Custom settings implement `FieldGroupSetting`, `BlockSetting` or `SettingsPageSetting`.
- `Registrar`, registering the declared classes once, building them when Meta Box reads the declarations, and rejecting an ID used twice, a missing `fields()` method, an attribute of another kind or a registration after Meta Box read the declarations. The `Integration` contract connects it to a framework, such as Pollora with pollora/metabox-bridge.

## 3.0.0 - 2026-10-16

### Changed

- The `Metabox` class is renamed `FieldGroup`, the name MB Builder and ACF give to a set of fields registered together: it is displayed as a meta box on post edit screens, and as a section on terms, users, settings pages or in the Customizer. Its methods are unchanged, and `Block` now extends `FieldGroup`. See the [upgrade guide](UPGRADE.md#from-pollorametabox-26-to-30).
- The meta boxes documentation page is now [Field groups](docs/field-groups.md).
- The upgrade guide from `amphibee/metabox-maker` 1.x targets 3.x and includes the rename.

## 2.6.0 - 2026-10-07

### Added

- Relationships (MB Relationships): `Relationship::make()` with `from()`, `to()` and `reciprocal()`, and `Side::posts()`, `Side::terms()` and `Side::users()` with `metaBox()`, `field()`, `adminColumn()` and `hasOne()`. Every setting of a side applies to the screens of its objects: the package moves the field settings to the side where Meta Box expects them. Relationships are registered on `mb_relationships_init`; a relationship declared too late, declared twice, or reciprocal between different objects throws a `LogicException`.
- Custom models (MB Custom Table): `MetaboxModel::make()` with `table()`, `labels()`, `menuIcon()`, `menuPosition()`, `parent()`, `showInMenu()`, `capability()` and `supports()` (`ModelSupport` enum), registered on `init`.
- `Location::models()`: a meta box located on custom models stores its values in their table, without `customTable()`.
- `customTable()` and `MetaboxModel::table()` accept a class with a `getTable()` method, such as an Eloquent model, and `prefix: false` for an existing table without the prefix. Table names are validated.
- Documentation page for custom tables, with Laravel migrations.

### Changed

- `customTable()` adds the WordPress table prefix of the current site to the table name: `customTable('events')` stores the values in `wp_events`. See the [upgrade guide](UPGRADE.md#from-pollorametabox-25-to-26).

## 2.5.0 - 2026-10-07

### Added

- Geolocation (MB Geolocation): `Metabox::geolocation()` with the API key, place types and countries (validated ISO codes, 5 at most), and `geoBinding()` and `addressField()` on input, `Textarea`, `Hidden`, `Select` and `SelectAdvanced` fields.
- `seoAnalysis()` on `Text`, `Textarea` and `Wysiwyg` fields, for MB Yoast SEO and MB Rank Math.
- `Column` layout element holding several fields in one column of the grid (Meta Box Columns), in meta boxes, blocks and tabs.
- Customizer sections without a settings page: `Metabox::customizer()`, with an optional panel and option name. `priority()` accepts an integer, the position of a Customizer section: it throws a `LogicException` on a meta box displayed elsewhere.
- `inputTooltip()` on the field types supported by Meta Box Tooltip. Other types throw a `LogicException`.
- `hideFromBlockBindings()`, `autofocus()` and `fieldName()` on every field.
- `SelectTree` field, and `tree()` and `collapse()` on `CheckboxList`, building hierarchical options from a nested array.
- `Backup` field, exporting and importing the values of a settings page.
- `addNew()` on `Post` and `User` fields (`Post` requires a single post type), and `toggleAllButton()` on `Post`, `Taxonomy` and `User` fields.
- `dateFormat()` and `autocomplete()` on `Datepicker` and `DatetimePicker`, `timeFormat()` on `DatetimePicker`, `autocomplete()` on `Timepicker`.
- `minLength()`, `maxLength()`, `autocomplete()` and `wrap()` on `Textarea`, with the `TextareaWrap` enum.
- `distractionFreeWriting()` on `Wysiwyg`.
- `Block::attributes()`, adding validated attributes to the block type while keeping the MB Blocks ones.
- `SettingsPage::tab()`, adding a tab with an optional icon, and a `tab` argument on `Location::settingsPages()`.
- `Metabox::context()` accepts the `Context` enum, as documented.

### Changed

- `jsOptions()` merges the options with the ones set before, by `jsOptions()` or by the methods setting a JavaScript option such as `timeFormat()`, instead of replacing them.

### Documentation

- The defaults that differ from Meta Box are documented, with how to get the Meta Box behavior: settings pages have one column (`columns(2)` for the sidebar layout) and blocks display their fields in the block body (`context('side')` for the block sidebar).

## 2.4.1 - 2026-10-07

### Fixed

- Block post type restrictions replaced "all blocks allowed" with the list of server-side registered blocks on every post type as soon as one block was restricted, which removed the blocks registered in JavaScript only. The allowed blocks are now changed only on the post types where a block is removed, and the filter no longer loops over every block for each restriction.

## 2.4.0 - 2026-10-07

### Added

- Typed `make()` signatures: `Field::make(string $name, string $id)`, `Metabox::make(string $title, string $id)`, `SettingsPage::make(string $pageTitle, string $id)`, `Heading::make(string $name = '')` and `Divider::make()`. They give IDE completion, static analysis and named arguments.
- `Input` base class for `Text`, `Email`, `Url`, `Number`, `Range` and `Password`, with `autocomplete()`, `minLength()`, `maxLength()` and `pattern()` on top of `size()`, `prepend()`, `append()` and `datalist()`.
- `Media` base class for the media library fields (`FileAdvanced`, `FileUpload`, `ImageAdvanced`, `ImageUpload`, `SingleImage`, `Video`), with `addTo()` on all of them.
- `markerDraggable()` on `GoogleMap` and `OpenStreetMap`, `iconBaseClass()` on `Icon`, `cloneEmptyStart()` on every field, `mimeType()` on `File` and `Image`, `imageSize()` on `Image`.
- `MediaPlacement`, `SwitchStyle` and `SettingsPageStyle` enums.
- A test keeps the documentation in sync with the public API.

### Changed

- `Email`, `Url`, `Number`, `Range` and `Password` extend `Input` instead of `Text`: they no longer have `type()`, and `Email`, `Url`, `Number` and `Range` are no longer instances of `Text`.
- `ImageAdvanced`, `ImageUpload` and `SingleImage` extend `Media` instead of `Image`, like in Meta Box: they no longer have `uploadDir()` and `uniqueFilenameCallback()`, which Meta Box ignores for media library fields.
- `ImageAdvanced::newImagePlacement()` is replaced by `addTo()`, available on every media library field.
- `Switcher::style()` only accepts `rounded` and `square`, and `SettingsPage::style()` only accepts `boxes` and `no-boxes`.
- `maxFileSize()` accepts a number of bytes as well as a string such as `10mb`.
- `Renderable` no longer declares `make()`.

### Fixed

- Documentation: wrong method names (`maxFileUpload()`, `maxStatus()`, `toggleAll()`, `timepickerOptions()`), undocumented methods, and upload fields described with the wrong storage.

## 2.3.0 - 2026-10-07

### Added

- `Metabox::include()`, `exclude()` (MB Include Exclude), `show()` and `hide()` (MB Show Hide), taking a `Rule` or a group of rules built with `Rule::all()` (AND) or `Rule::any()` (OR).
- `Rule` constructors for every rule of both extensions, including the `capability` rule. Rules unsupported by the method, and rules with the same key combined with AND, throw an exception.

## 2.2.0 - 2026-10-07

### Added

- Conditional logic (Meta Box Conditional Logic): `visibleWhen()`, `orVisibleWhen()`, `hiddenWhen()` and `orHiddenWhen()` on fields, headings, dividers and meta boxes, accepting a field instance or an ID, and `Metabox::toggleType()`. Operators are validated.
- `columns()` on fields (Meta Box Columns).
- `tooltip()` on fields (Meta Box Tooltip).
- `adminColumn()` on fields (MB Admin Columns), with named arguments.
- `maxCharacters()` and `maxWords()` on Text, Textarea and Wysiwyg fields (MB Text Limiter).
- `Field::getId()`.
- `ToggleType`, `TooltipPosition` and `AdminColumnLink` enums.
- Documentation page for the extensions.

## 2.1.0 - 2026-10-07

### Added

- `BlockEditor` field (`block_editor`), with `allowedBlocks()`, `height()` and `toolbarPosition()`.
- `Link` field.
- `Text::type()` accepts the `search`, `tel`, `month`, `week` and `datetime-local` input types.
- Meta box tab settings: `tabStyle()`, `tabWrapper()`, `tabDefaultActive()` and `tabRemember()`. `TabStyle::BOX` is added.
- `Metabox::validation()` for validation rules and messages.
- `Metabox::revision()` (MB Revision).
- `Metabox::customTable()` and `Metabox::storageType()` (MB Custom Table). `storageType()` was only available on blocks.
- `BlockMode` and `ToolbarPosition` enums.

### Changed

- `Block::mode()` only accepts `edit` and `preview`, and throws an exception for any other value. Meta Box displayed blocks with another value, such as `auto`, in preview mode: see the [upgrade guide](UPGRADE.md#from-pollorametabox-20-to-21).

### Documentation

- Complete list of the meta box and block methods.

## 2.0.0 - 2026-10-07

The package moves to the Pollora organization. Follow the [upgrade guide](UPGRADE.md) to migrate from `amphibee/metabox-maker`.

### Changed

- The package is renamed `pollora/metabox`, and the namespace `AmphiBee\MetaboxMaker` is now `Pollora\Metabox`. There is no compatibility layer for the former names.
- License: MIT instead of GPL-2.0-or-later.
- PHP 8.2 or later is required.
- The documentation moves to `docs/`, with one page per topic.

### Removed

- The `Fieldset` field: use `FieldsetText`.
- `Button::setAttributes()`: use `attributes()`, available on every field.
- `Taxonomy::taxonomy()`: use `taxonomies()`.

## 1.0.0 - 2026-10-07

First tagged release. Projects previously required `dev-main`: switch to `^1.0` and read the upgrade notes below.

### Upgrade notes

- **Taxonomy / TaxonomyAdvanced fields now use the taxonomy you pass.** The field emitted a `taxonomies` key that Meta Box ignores, so it always listed **categories**, whatever was passed to `taxonomies()`. After upgrading:
  - fields declared with a real taxonomy now list the right terms, but values saved so far are category IDs and may need to be cleaned up or migrated;
  - fields declared with something that is not a taxonomy (for instance `taxonomies(['post'])`) only worked by accident and now list nothing: use `taxonomies('category')`;
  - the `->setting('taxonomy', ...)` workaround still works and can be replaced by `->taxonomy(...)`.
- **SettingsPage menu icons are now applied.** `iconType()`, `icon()` and `iconSvg()` are converted to the `icon_url` setting read by MB Settings Page. They had no effect before, so settings pages may show a new menu icon.
- **`Context::Side` and `Context::FormTop`** now hold `side` and `form_top`. The string values `'side'` and `'normal'` behave as before.
- **Block default category** is `design` instead of `layout`, which no longer exists since WordPress 5.8. Blocks that call `category()` are not affected.
- **Calling `ajax()`** on Post, Taxonomy and User fields switches the default `select` field type to `select_advanced`, the only one Meta Box supports AJAX with.
- **Strict types**: library and test files now declare `strict_types`. This only affects code calling the library from a file that declares it too.

### Fixed

- Taxonomy and TaxonomyAdvanced fields emit `taxonomy` instead of `taxonomies`.
- GoogleMap emits the `map` type instead of `google_map`, which Meta Box rendered as a plain text input.
- Fieldset emits `fieldset_text`: Meta Box has no `fieldset` type. `Fieldset` is deprecated in favor of `FieldsetText`.
- `Context::Side` was `seamless` and `Context::FormTop` was `side`.
- `Metabox::class(null)` threw a `TypeError`.
- `Location::default()` emitted `post_type` instead of `post_types`. The tests and docs used the same wrong key.
- SettingsPage icon settings were ignored at runtime.
- SettingsPage is not registered twice when built inside the `mb_settings_pages` filter, like Metabox.
- Slider forced its initial position to 0 and ignored `default()`.
- Block post type restrictions matched any block whose name contained the block ID, such as `meta-box/hero-banner` for `hero`. They now match the exact `meta-box/{id}` name, and `restrictToPostTypes()` and `excludePostTypes()` combine instead of overwriting each other.
- `ajax()` was silently ignored with the default `select` field type.
- Group and Tab refused Heading and Divider.

### Added

- `attributes()` and `attribute()` on every field. They were only available on Button, as `setAttributes()`.
- `class()`, `before()`, `after()`, `saveField()`, `sanitizeCallback()`, `hideFromRest()` and `hideFromFront()` on every field.
- `Taxonomy::taxonomy()`, an alias of `taxonomies()`.
- `Location::postTypes()`, `taxonomies()`, `settingsPages()`, `user()`, `comment()` and `andWhere()`.
- Documentation of the settings shared by all fields (`doc/CommonSettings.md`).

### Internal

- The test suite runs again: `doing_filter()` had no stub since the "already in the rwmb_meta_boxes filter" fix.
- PHPStan level 5 with the WordPress extension, Pint configuration and GitHub workflows for tests and coding standards, aligned with the Pollora packages.
- `laravel/pint` moved from `require` to `require-dev`.
