# Attributes

A field group, a block or a settings page can be declared by a class with attributes, instead of a call to `make()`. The attribute gives the title and the ID, a few more attributes give the frequent settings, and the class returns its fields:

```php
use App\Cms\PostTypes\Solution;
use Pollora\Metabox\Attributes\FieldGroup;
use Pollora\Metabox\Attributes\FieldGroup\PostTypes;
use Pollora\Metabox\Fields\Color;
use Pollora\Metabox\Fields\Text;

#[FieldGroup('Solution fields')]
#[PostTypes(Solution::class)]
final class SolutionFields
{
    public function fields(): array
    {
        return [
            Color::make('Color', 'color'),
            Text::make('Tagline', 'tagline')->required(),
        ];
    }
}
```

In a [Pollora](https://pollora.dev) application, [pollora/metabox-bridge](https://github.com/Pollora/metabox-bridge) discovers these classes: there is nothing else to write. Elsewhere, register them once, before the `init` action, priority 20:

```php
use Pollora\Metabox\Attributes\Registrar;

Registrar::register(SolutionFields::class, AdvancedQuote::class, ThemeOptions::class);
```

Compared with `make()`:

- Each class is registered once, however many times it is discovered or passed to `Registrar::register()`.
- `fields()` is called when Meta Box reads the declarations, at `init` priority 20: it can use what plugins and the theme set up before.
- The declarations are checked when the classes are registered: an ID used by two field groups or blocks, a missing `fields()` method or an attribute that does not apply to the declaration throws a `LogicException`. A field group or block registered after Meta Box read the declarations does too.

## Field groups

`#[FieldGroup(string $title, ?string $id = null)]` declares a field group. Without an ID, the class name in snake case is used: `solution_fields` for `SolutionFields`.

| Attribute | Equivalent |
|---|---|
| `#[PostTypes('solution', 'page')]` | `Location::postTypes(['solution', 'page'])` |
| `#[Taxonomies('category')]` | `Location::taxonomies('category')` |
| `#[SettingsPages('theme-options')]` | `Location::settingsPages('theme-options')` |

These attributes are in the `Pollora\Metabox\Attributes\FieldGroup` namespace. Several of them display the field group on each target. A value can also be a class:

- a class declared with `#[SettingsPage]`, for `#[SettingsPages(ThemeOptions::class)]`;
- a class declared with Pollora's `#[PostType]` or `#[Taxonomy]`, with pollora/metabox-bridge: the slug is the one Pollora registers.

A class declaring nothing for the target throws a `LogicException`.

## Blocks

`#[Block(string $title, string $id)]` declares a block. The ID is the block name stored in the post content, so it is always given.

| Attribute | Equivalent |
|---|---|
| `#[Icon('format-quote')]` | `->icon('format-quote')` |
| `#[Description('A quote with its author')]` | `->description('A quote with its author')` |
| `#[Category('theme')]` | `->category('theme')` |
| `#[Mode(BlockMode::Preview)]` | `->mode(BlockMode::Preview)` |
| `#[View('blocks.quote')]` | A render callback rendering the view |

These attributes are in the `Pollora\Metabox\Attributes\Block` namespace.

```php
use Pollora\Metabox\Attributes\Block;
use Pollora\Metabox\Attributes\Block\Description;
use Pollora\Metabox\Attributes\Block\Icon;

#[Block('Advanced quote', 'advanced-quote')]
#[Icon('align-center')]
#[Description('A quote with an image')]
final class AdvancedQuote
{
    public function fields(): array
    {
        return [
            SingleImage::make('Image', 'image'),
            Text::make('Quote', 'quote'),
        ];
    }
}
```

A view needs an integration rendering it: pollora/metabox-bridge renders Blade views, and gives every block a default view and category. The view receives:

- `$attributes`: the field values, as stored, e.g. the ID of an image;
- `$block`: the attributes of the block, with the formatted field values (`mb_get_block_field()`) and the `anchor`;
- `$isPreview`: whether the block is rendered in the editor;
- `$postId`: the ID of the post being rendered.

## Settings pages

`#[SettingsPage(string $title, string $id)]` declares a settings page. Its fields are field groups displayed on it with `#[SettingsPages]`.

| Attribute | Equivalent |
|---|---|
| `#[Icon('admin-generic')]` | `->icon('admin-generic')` |
| `#[Position(2)]` | `->position(2)` |
| `#[OptionName('theme')]` | `->optionName('theme')` |
| `#[SubmitButton('Save')]` | `->submitButton('Save')` |

These attributes are in the `Pollora\Metabox\Attributes\SettingsPage` namespace.

```php
use Pollora\Metabox\Attributes\FieldGroup;
use Pollora\Metabox\Attributes\FieldGroup\SettingsPages;
use Pollora\Metabox\Attributes\SettingsPage;
use Pollora\Metabox\Attributes\SettingsPage\Icon;
use Pollora\Metabox\Attributes\SettingsPage\OptionName;

#[SettingsPage('Theme options', 'theme-options')]
#[Icon('admin-generic')]
#[OptionName('theme')]
final class ThemeOptions {}

#[FieldGroup('Footer')]
#[SettingsPages(ThemeOptions::class)]
final class FooterOptions
{
    public function fields(): array
    {
        return [Text::make('Copyright', 'copyright')];
    }
}
```

## Other settings

A `configuring()` method receives the builder, after the attributes and the fields, with all its methods:

```php
use Pollora\Metabox\Attributes\FieldGroup;
use Pollora\Metabox\Rules\Rule;

#[FieldGroup('Homepage')]
final class HomepageFields
{
    public function fields(): array
    {
        return [/* ... */];
    }

    public function configuring(\Pollora\Metabox\FieldGroup $group): void
    {
        $group->priority('high')
            ->include(Rule::template('templates/home.php'));
    }
}
```

The builder has the same short name as the attribute: write its full name, as for the `configuring()` method of a Pollora post type, or import it with an alias.

A setting used by many classes can be an attribute of its own. It implements `FieldGroupSetting`, `BlockSetting` or `SettingsPageSetting` from `Pollora\Metabox\Attributes\Contracts`:

```php
use Attribute;
use Pollora\Metabox\Attributes\Contracts\FieldGroupSetting;
use Pollora\Metabox\FieldGroup;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class HighPriority implements FieldGroupSetting
{
    public function configure(FieldGroup $group): void
    {
        $group->priority('high');
    }
}
```

A `FieldGroupSetting` also applies to blocks.

## Integrations

An integration connects the declared classes to a framework. It implements `Pollora\Metabox\Attributes\Contracts\Integration`: how the classes are instantiated, the defaults applied before the attributes, the classes used as targets, and how block views are rendered. pollora/metabox-bridge provides the Pollora one; another is set with `Registrar::integrate()`.

---

**Previous:** [Custom tables](custom-tables.md)
