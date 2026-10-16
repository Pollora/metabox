# Upgrade guide

## From pollora/metabox 3.0 to 3.1

Nothing needs to change: 3.1 adds the [attributes](docs/attributes.md). Classes declared with `make()` keep working, and both can be used in the same project.

## From pollora/metabox 2.6 to 3.0

The `Metabox` class is now `FieldGroup`, the name MB Builder and ACF give to a set of fields: on a post edit screen it is displayed as a meta box, elsewhere as a section. Its methods are unchanged. `Block` now extends `FieldGroup`.

| Before | After |
|---|---|
| `use Pollora\Metabox\Metabox;` | `use Pollora\Metabox\FieldGroup;` |
| `Metabox::make('Event', 'event')` | `FieldGroup::make('Event', 'event')` |
| `function (Metabox $metabox)` | `function (FieldGroup $metabox)` |
| `$box instanceof Metabox` | `$box instanceof FieldGroup` |

`MetaboxModel`, `SettingsPage`, `Block`, `Location` and the `Pollora\Metabox` namespace keep their names.

Rename the class in the files importing it. Run this from the project root, on the directories that hold your code:

```bash
grep -rlE 'Pollora\\+Metabox\\+Metabox\b' app config --include='*.php' \
  | xargs sed -i -E 's/\bMetabox\b([^\\]|$)/FieldGroup\1/g'
```

On macOS, use `sed -i ''` instead of `sed -i`. The command only changes `Metabox` when it is not followed by a backslash, so the `Pollora\Metabox` namespace and `MetaboxModel` are kept. Then run your code formatter, since the `use` statements may no longer be sorted, and check that nothing is left:

```bash
# This must print nothing
grep -rnE 'Pollora\\+Metabox\\+Metabox\b' app config --include='*.php'
```

## From pollora/metabox 2.5 to 2.6

`customTable()` now adds the WordPress table prefix to the table name, like a Laravel database connection: `customTable('events')` stores the values in `wp_events`. Check every call:

| Before | After |
|---|---|
| `->customTable('wp_events')` or `->customTable($wpdb->prefix.'events')` | `->customTable('events')` |
| `->customTable('events')`, for a table really named `events` | `->customTable('events', prefix: false)` |
| `->storageType('custom_table')->setting('table', 'wp_events')` | `->customTable('events')` |
| `->setting('models', ['transaction'])` with `->customTable(...)`, after `mb_register_model()` | `MetaboxModel::make('transaction')->table('transactions')` and `->location(Location::models('transaction'))` |

Find them with:

```bash
grep -rnE "customTable\(|custom_table|mb_register_model" app config --include='*.php'
```

Nothing else needs to change: the rest of 2.6 only adds classes.

Relationships registered with `MB_Relationships_API::register()` keep working. They can be replaced with `Relationship`, which validates them. Note that the `field` settings move to the other side: in the Meta Box array, they configure the field displayed on the screen of the other side, while a `Side` configures the screens of its own objects.

| Before | After |
|---|---|
| `add_action('mb_relationships_init', fn () => MB_Relationships_API::register(['id' => 'posts_to_pages', 'from' => 'post', 'to' => 'page']))` | `Relationship::make('posts_to_pages')->from(Side::posts())->to(Side::posts('page'))` |
| `'from' => ['object_type' => 'term', 'taxonomy' => 'category']` | `->from(Side::terms('category'))` |
| `'from' => ['object_type' => 'user']` | `->from(Side::users())` |
| `'from' => ['meta_box' => ['title' => 'Pages', 'context' => 'normal']]` | `->from(Side::posts()->metaBox(title: 'Pages', context: 'normal'))` |
| `'to' => ['field' => ['max_clone' => 3, 'query_args' => [...]]]` | `->from(Side::posts()->field(max: 3, queryArgs: [...]))`: on the from side |
| `'from' => ['admin_column' => 'after title']` | `->from(Side::posts()->adminColumn(after: 'title'))` |
| `'from' => ['has_one_relationship' => true]` | `->from(Side::posts('product')->hasOne())` |
| `'reciprocal' => true` | `->reciprocal()` |

Find them with:

```bash
grep -rn "MB_Relationships_API::register" app config --include='*.php'
```

## From pollora/metabox 2.4 to 2.5

Nothing needs to change: 2.5 adds methods and fields.

`jsOptions()` now merges its options with the ones set before instead of replacing them. A field calling `jsOptions()` once, as usual, behaves the same. A field calling it after `timeFormat()`, `stepMinute()` or another method setting a JavaScript option now keeps that option: Meta Box ignored it before. Find these calls with:

```bash
grep -rnE "\->jsOptions\(" app config --include='*.php'
```

Settings passed with `setting()` keep working. They can be replaced with the new methods, which validate their values:

| Before | After |
|---|---|
| `->setting('geo', ['api_key' => 'KEY'])` on a meta box | `->geolocation(apiKey: 'KEY')` |
| `->setting('binding', 'locality')` | `->geoBinding('locality')` |
| `->setting('address_field', 'address_ho')` on a text field | `->addressField('address_ho')` |
| `->setting('add_to_wpseo_analysis', true)` or `->setting('rank_math_analysis', true)` | `->seoAnalysis()` |
| `->setting('columns', ['column-1' => 4])` with `->setting('column', 'column-1')` on fields | `Column::make(4)->fields([...])` |
| `->setting('panel', '')` | `->customizer()` |
| `->setting('tooltip_input', 'Help')` | `->inputTooltip('Help')` |
| `->setting('hide_from_block_bindings', true)` | `->hideFromBlockBindings()` |
| `->setting('field_name', 'name')`, `->setting('autofocus', true)` | `->fieldName('name')`, `->autofocus()` |
| `Select::make(...)->setting('type', 'select_tree')`, options with `parent` keys and `flatten => false` | `SelectTree::make(...)->tree([...])` |
| `->setting('type', 'backup')` | `Backup::make()` |
| `->setting('add_new', true)` on `Post` or `User` | `->addNew()` |
| `->jsOptions(['dateFormat' => 'dd/mm/yy'])` | `->dateFormat('dd/mm/yy')` |
| `->setting('wrap', 'hard')`, `->setting('minlength', 10)` on `Textarea` | `->wrap('hard')`, `->minLength(10)` |
| `->tinymce(...)` with `->setting('options', ['dfw' => false])` | `->distractionFreeWriting(false)` |
| `->setting('attributes', [...])` on a block | `->attributes([...])`: the MB Blocks attributes are kept |
| `->tabs(['general' => ['label' => 'General', 'icon' => 'dashicons-admin-generic']])` | `->tab('general', 'General', icon: 'dashicons-admin-generic')` |
| `Location::settingsPages('options')->andWhere('tab', 'general')` | `Location::settingsPages('options', tab: 'general')` |

## From pollora/metabox 2.3 to 2.4

Most projects have nothing to change. Check the following calls:

| Before | After |
|---|---|
| `Email::make(...)->type(...)`, same for `Url`, `Number`, `Range` | Use the class of the wanted type, or `Text::make(...)->type(...)` |
| `$field instanceof Text` for an `Email`, `Url`, `Number` or `Range` field | `$field instanceof Input` |
| `ImageAdvanced`, `ImageUpload` or `SingleImage` with `->uploadDir()` or `->uniqueFilenameCallback()` | Remove the call: Meta Box ignored it for these fields. To upload to a custom directory, use `Image` |
| `ImageAdvanced::make(...)->newImagePlacement('beginning')` | `->addTo('beginning')` or `->addTo(MediaPlacement::Beginning)` |
| `Switcher::make(...)->style(...)` with a value other than `rounded` or `square` | `rounded` or `square`: Meta Box ignored other values |
| `SettingsPage::make(...)->style(...)` with a value other than `boxes` or `no-boxes` | `boxes` or `no-boxes` |

Find them with:

```bash
grep -rnE "(Email|Url|Number|Range)::make\([^;]*->type\(|instanceof Text\b|->uploadDir\(|->uniqueFilenameCallback\(|->newImagePlacement\(|->style\(" app config --include='*.php'
```

`->uploadDir()` and `->style()` also match valid calls, on `File` and `Image` or with a supported value: only change the ones listed above.

## From pollora/metabox 2.2 to 2.3

Nothing needs to change: 2.3 only adds methods.

`include`, `exclude`, `show` and `hide` settings passed with `setting()` keep working. Note that Meta Box combines their rules with OR by default: use `Rule::any()` to keep the same behavior when replacing them.

| Before | After |
|---|---|
| `->setting('include', ['template' => ['front-page.php']])` | `->include(Rule::template('front-page.php'))` |
| `->setting('exclude', ['ID' => [12, 14], 'is_child' => true])` | `->exclude(Rule::any(Rule::postIds(12, 14), Rule::isChild()))` |
| `->setting('show', ['relation' => 'AND', 'post_format' => ['video'], 'category' => ['News']])` | `->show(Rule::all(Rule::postFormat('video'), Rule::category('News')))` |

## From pollora/metabox 2.1 to 2.2

Nothing needs to change: 2.2 only adds methods.

Settings passed with `setting()` keep working. They can be replaced with the new methods, which validate their values:

| Before | After |
|---|---|
| `->setting('visible', ['link_type', '=', 'page'])` | `->visibleWhen('link_type', 'page')` |
| `->setting('hidden', ['is_toggle', '1'])` | `->hiddenWhen('is_toggle', '1')` |
| `->setting('columns', 6)` | `->columns(6)` |
| `->setting('tooltip', 'Help text')` | `->tooltip('Help text')` |
| `->setting('admin_columns', 'after title')` | `->adminColumn(after: 'title')` |
| `->setting('limit', 160)` | `->maxCharacters(160)` |

## From pollora/metabox 2.0 to 2.1

`Block::mode()` only accepts the values Meta Box supports, `edit` and `preview`, and throws an `InvalidArgumentException` for any other value. Meta Box displayed blocks with another value, such as `auto`, in preview mode: replace it with `preview` to keep the same behavior.

```bash
grep -rn "\->mode('auto')" app config --include='*.php'
```

```php
// Before
Block::make('Hero', 'hero')->mode('auto');

// After
Block::make('Hero', 'hero')->mode('preview');
```

Nothing else needs to change: 2.1 only adds methods and fields.

## From amphibee/metabox-maker 1.x to pollora/metabox 3.x

`amphibee/metabox-maker` is now `pollora/metabox`, and the `AmphiBee\MetaboxMaker` namespace is now `Pollora\Metabox`. Version 2.0 has no compatibility layer: the former names no longer exist, so every reference must be renamed. The package API is otherwise unchanged, apart from the `Metabox` class, renamed `FieldGroup` in 3.0 (step 3), and the few removals listed in step 4.

`amphibee/metabox-maker` is abandoned and stays at 1.0.0. A project not migrated yet keeps working on `^1.0`.

### 1. Check the prerequisites

- PHP 8.2 or later.
- `amphibee/metabox-maker` 1.0.0, with its [upgrade notes](CHANGELOG.md#100---2026-10-07) applied. Projects still on `dev-main` or `0.9` must go through 1.0 first: it changes what Taxonomy fields display and how settings page icons are set.

### 2. Replace the package

```bash
composer remove amphibee/metabox-maker
composer require pollora/metabox:^3.0
```

Until step 3 is done, the project fails with `Class "AmphiBee\MetaboxMaker\..." not found`: do both steps together.

### 3. Rename the namespace

The new namespace keeps the same class names and sub-namespaces (`Fields`, `Enums`, `Fields\Settings`…), so a prefix replacement is enough. Run this from the project root, on the directories that hold your code (here `app` and `config`; for a theme or a plugin, `web/app/themes/<theme>` or the plugin directory):

```bash
grep -rlF 'AmphiBee\' app config --include='*.php' \
  | xargs sed -i -e 's/AmphiBee\\\\MetaboxMaker/Pollora\\\\Metabox/g' -e 's/AmphiBee\\MetaboxMaker/Pollora\\Metabox/g'
```

On macOS, use `sed -i ''` instead of `sed -i`.

The two expressions cover every form of the name:

| Before | After |
|---|---|
| `use AmphiBee\MetaboxMaker\Fields\Text;` | `use Pollora\Metabox\Fields\Text;` |
| `\AmphiBee\MetaboxMaker\SettingsPage::class` | `\Pollora\Metabox\SettingsPage::class` |
| `'AmphiBee\MetaboxMaker\Location'` | `'Pollora\Metabox\Location'` |
| `"AmphiBee\\MetaboxMaker\\Block"` | `"Pollora\\Metabox\\Block"` |
| `@var \AmphiBee\MetaboxMaker\Fields\Group` | `@var \Pollora\Metabox\Fields\Group` |

The `use` statements now start with `Pollora` instead of `AmphiBee`, so they may no longer be sorted: run your code formatter (for instance `vendor/bin/pint`).

Then rename the `Metabox` class to `FieldGroup`, with the command of the [2.6 to 3.0 section](#from-pollorametabox-26-to-30). Run it after the namespace replacement: it only changes the files importing `Pollora\Metabox\Metabox`.

### 4. Replace the removed methods and classes

| Removed | Use instead |
|---|---|
| `Fieldset` field | `FieldsetText`, with `options()` instead of `inputs()` |
| `Button::setAttributes()` | `attributes()`, available on every field |
| `Taxonomy::taxonomy()` / `TaxonomyAdvanced::taxonomy()` | `taxonomies()` |
| `Block::mode('auto')`, or any value other than `edit` and `preview` (since 2.1) | `mode('preview')`: Meta Box already displayed these blocks in preview mode |
| `ImageAdvanced::newImagePlacement()` (since 2.4) | `addTo()` |
| `uploadDir()` / `uniqueFilenameCallback()` on `ImageAdvanced`, `ImageUpload` or `SingleImage` (since 2.4) | Remove the call: Meta Box ignored it for these fields |
| `type()` on `Email`, `Url`, `Number` or `Range` (since 2.4) | The class of the wanted type, or `Text::make(...)->type(...)` |

Find them with:

```bash
grep -rnE "Fields\\\\Fieldset;|Fieldset::make|->setAttributes\(|->taxonomy\(|->mode\('auto'\)|->newImagePlacement\(|->uploadDir\(|->uniqueFilenameCallback\(" app config --include='*.php'
```

`->taxonomy(` can also match unrelated code: only change the calls made on a Taxonomy or TaxonomyAdvanced field.

### 5. Check the result

```bash
# No former name left: this must print nothing
grep -rnF 'AmphiBee\' app config --include='*.php' | grep MetaboxMaker
grep -rnE 'Pollora\\+Metabox\\+Metabox\b' app config --include='*.php'

composer dump-autoload
```

Then open the admin screens that use meta boxes, blocks and settings pages, and check that they display their fields.
