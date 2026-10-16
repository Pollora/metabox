# Extensions

Dedicated methods for the settings of the Meta Box extensions. Each one needs its extension, included in Meta Box AIO. For the other extensions, pass their settings with `setting()`.

## Conditional logic

Shows or hides a field, a heading, a divider or a whole field group depending on the value of other fields. Requires [Meta Box Conditional Logic](https://docs.metabox.io/extensions/meta-box-conditional-logic/).

### Example

```php
<?php

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Number;
use Pollora\Metabox\Fields\Post;
use Pollora\Metabox\Fields\Select;
use Pollora\Metabox\Fields\Url;

$linkType = Select::make('Link type', 'link_type')
    ->options(['page' => 'Page', 'custom' => 'Custom URL']);

FieldGroup::make('Call to action', 'cta')
    ->fields([
        $linkType,
        Post::make('Page', 'page')->postType('page')->visibleWhen($linkType, 'page'),
        Url::make('URL', 'url')->visibleWhen($linkType, 'custom'),
        Number::make('Discount', 'discount')->visibleWhen('price', '>', 100),
    ]);
```

With two arguments, the field must equal the value. With three, the second argument is the operator. The field can be given as a field instance, which keeps the condition right if the field ID changes, or as an ID. Any input name works too, such as `page_template`, `post_format` or `_thumbnail_id`: see the [Meta Box documentation](https://docs.metabox.io/extensions/meta-box-conditional-logic/#toggle-by-other-elements).

### Combining conditions

Successive calls must all match (AND). The `or` methods make any of them match (OR):

```php
// Visible when the brand is Apple AND the year is between 2010 and 2015
Text::make('Model', 'model')
    ->visibleWhen('brand', 'Apple')
    ->visibleWhen('year', 'between', [2010, 2015]);

// Hidden when the brand is Apple OR Samsung
Text::make('Model', 'model')
    ->hiddenWhen('brand', 'Apple')
    ->orHiddenWhen('brand', 'Samsung');
```

Meta Box applies a single relation to all the conditions of an element, so mixing AND and OR throws a `LogicException`.

### Methods

- **`visibleWhen(Field|string $field, mixed $operator, mixed $value = null)`**: Shows the element when the condition matches.
- **`orVisibleWhen(...)`**: Same, combined with the previous conditions with OR.
- **`hiddenWhen(...)`** / **`orHiddenWhen(...)`**: Hides the element when the condition matches.
- **`FieldGroup::toggleType(string|ToggleType $type)`**: Sets how fields are shown and hidden: `display` (default, the next fields move up), `visibility` (hidden fields keep their space), `slide` or `fade`.

Operators: `=`, `!=`, `>`, `>=`, `<`, `<=`, `in`, `contains`, `between`, `starts with`, `ends with`, `match`, and the `not` forms (`not in`, `not contains`, `not between`, `not starts with`, `not ends with`, `not match`). Any other operator throws an `InvalidArgumentException`.

## Columns

Lays fields out on a 12-column grid. Requires [Meta Box Columns](https://docs.metabox.io/extensions/meta-box-columns/).

```php
Text::make('First name', 'first_name')->columns(6),
Text::make('Last name', 'last_name')->columns(6),
```

- **`columns(int $columns)`**: Sets how many columns the field spans, from 1 to 12.

To put several fields in one column, use a [`Column`](layout-fields.md#column):

```php
Column::make(4)->fields([
    Text::make('Name', 'name'),
    Email::make('Email', 'email'),
]),
Column::make(8)->fields([
    Textarea::make('Message', 'message'),
]),
```

## Tooltip

Shows a tooltip next to the field label. Requires [Meta Box Tooltip](https://docs.metabox.io/extensions/meta-box-tooltip/).

```php
use Pollora\Metabox\Enums\TooltipPosition;

Number::make('Price', 'price')->tooltip('Price including VAT');

Number::make('Price', 'price')
    ->tooltip('Price <b>including</b> VAT', icon: 'help', position: TooltipPosition::Right, allowHtml: true);
```

- **`tooltip(string $content, ?string $icon = null, string|TooltipPosition|null $position = null, bool $allowHtml = false)`**: The icon is `info` (default), `help`, a Dashicons name or an image URL. The position is `top` (default), `bottom`, `left` or `right`.
- **`inputTooltip(...)`**: Shows the tooltip next to the input instead of the label, with the same arguments. Meta Box Tooltip supports it on `Text` (type `text`), `Email`, `Url`, `Number`, `Password`, `Datepicker`, `DatetimePicker`, `Timepicker`, `Select` and `SelectAdvanced` fields only: the method exists on these classes, and throws a `LogicException` for an unsupported type, such as `Text::make(...)->type('tel')` or an `Icon` field.

## Admin columns

Shows the field value in a column of the admin list of posts, terms or users. Requires [MB Admin Columns](https://docs.metabox.io/extensions/mb-admin-columns/).

```php
use Pollora\Metabox\Enums\AdminColumnLink;

Text::make('Subtitle', 'subtitle')->adminColumn();

Number::make('Price', 'price')->adminColumn(
    after: 'title',
    sortable: 'numeric',
    searchable: true,
    link: AdminColumnLink::Edit,
    prepend: '$',
);
```

- **`adminColumn(...)`**: Without arguments, adds the column at the end. Named arguments:
  - `before`, `after` or `replace`: the ID of an existing column, e.g. `title` or `date`. Only one of them.
  - `title`: the column title. Defaults to the field name.
  - `sortable`: `true` to sort by value, `'numeric'` to sort numerically.
  - `searchable`: whether posts can be searched by this value.
  - `filterable`: whether posts can be filtered by this value. Taxonomy fields only.
  - `link`: link the value to the post `edit` screen or `view` page.
  - `width`: the column width, as a CSS value.
  - `prepend` / `append`: HTML displayed before or after the value.

## Text limiter

Limits the length of a `Text`, `Textarea` or `Wysiwyg` field. Requires [MB Text Limiter](https://docs.metabox.io/extensions/meta-box-text-limiter/).

```php
Text::make('SEO title', 'seo_title')->maxCharacters(60);
Textarea::make('Summary', 'summary')->maxWords(40);
```

- **`maxCharacters(int $limit)`**: Limits the number of characters.
- **`maxWords(int $limit)`**: Limits the number of words.

MB Text Limiter ignores the other input types: on a `Text` field whose `type()` is changed, for instance to `email`, both methods throw a `LogicException`. To limit other inputs, use `maxLength()`, which the browser enforces without a counter.

## Geolocation

Fills fields from an address selected in an autocomplete field. Requires [MB Geolocation](https://docs.metabox.io/extensions/meta-box-geolocation/).

The autocomplete field is a text field whose ID starts with `address`. When an address is selected, the fields named after an address component, such as `locality` or `postal_code`, are filled, and a map field moves its marker.

```php
FieldGroup::make('Venue', 'venue')
    ->geolocation(apiKey: 'GOOGLE_MAPS_API_KEY', countries: ['fr', 'be'])
    ->fields([
        Text::make('Address', 'address'),
        Text::make('City', 'locality'),
        Text::make('Department', 'department')->geoBinding('administrative_area_level_2'),
        Hidden::make('Country code', 'country_code')->geoBinding('short:country'),
        GoogleMap::make('Map', 'map')->addressField('address'),
    ]);
```

- **`FieldGroup::geolocation(?string $apiKey = null, array $types = [], string|array $countries = [])`**: Enables the autocomplete. Without an API key, and without a Google Maps field, OpenStreetMap is used. `$types` restricts the Google suggestions to place types, e.g. `['establishment']` or `['(cities)']`. `$countries` restricts them to at most 5 countries, as ISO 3166-1 alpha-2 codes: another value throws an exception.
- **`geoBinding(string $binding, bool $bindIfEmpty = true)`**: Fills the field with an address component when its ID is not the component name: a component (`locality`), its short name (`short:country`), or an expression combining components (`'postal_code + " " + locality'`). With `bindIfEmpty: false`, an empty component keeps the current value. Available on input, `Textarea`, `Hidden`, `Select` and `SelectAdvanced` fields.
- **`addressField(string $addressField)`**: When the field group has several autocomplete fields, binds the field to one of them, e.g. the head office address and the branch address.

## SEO analysis

Adds the field content to the content analyzed by Yoast SEO or Rank Math. Requires [MB Yoast SEO](https://docs.metabox.io/extensions/meta-box-yoast-seo/) or [MB Rank Math](https://docs.metabox.io/extensions/mb-rank-math/).

```php
Wysiwyg::make('Introduction', 'introduction')->seoAnalysis(),
```

- **`seoAnalysis(bool $analyze = true)`**: Available on `Text`, `Textarea` and `Wysiwyg` fields, including inside groups. It sets the keys of both extensions, so it works with either plugin.

## Include, exclude, show and hide

Registers or displays a whole field group depending on the post, the user or the edit screen. Requires [MB Include Exclude](https://docs.metabox.io/extensions/meta-box-include-exclude/) for `include()` and `exclude()`, and [MB Show Hide](https://docs.metabox.io/extensions/meta-box-show-hide/) for `show()` and `hide()`.

```php
<?php

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Rules\Rule;

FieldGroup::make('Homepage', 'homepage')
    ->include(Rule::template('front-page.php'));

FieldGroup::make('Editors only', 'editors')
    ->include(Rule::all(Rule::userRole('editor', 'administrator'), Rule::template('landing.php')))
    ->exclude(Rule::any(Rule::postIds(12, 14), Rule::isChild()));

FieldGroup::make('Video', 'video')
    ->show(Rule::postFormat('video'));
```

`include()` and `exclude()` are checked when the edit screen loads: the field group is registered or not. `show()` and `hide()` are applied in the browser and follow the changes made while editing, such as a new page template or post format.

Combine rules with `Rule::all()` (every rule must match) or `Rule::any()` (one rule is enough). A rule accepts several values and matches any of them: `Rule::userRole('editor', 'administrator')`.

### Methods

- **`include(Rule|RuleGroup $rule)`** / **`exclude(Rule|RuleGroup $rule)`**: Registers, or does not register, the field group when the rule matches.
- **`show(Rule|RuleGroup $rule)`** / **`hide(Rule|RuleGroup $rule)`**: Displays or hides the field group when the rule matches.

### Rules

Rule | `include` / `exclude` | `show` / `hide`
--- | :---: | :---:
`Rule::postIds(int ...$ids)` | ✓ |
`Rule::parentIds(int ...$ids)` | ✓ |
`Rule::slugs(string ...$slugs)` | ✓ |
`Rule::template(string ...$templates)` | ✓ | ✓
`Rule::postFormat(string ...$formats)` | | ✓
`Rule::category(int\|string ...$categories)` | ✓ | ✓ (IDs and names, not slugs)
`Rule::tag(int\|string ...$tags)` | ✓ |
`Rule::terms(string $taxonomy, int\|string ...$terms)` | ✓ | ✓ (IDs and names, not slugs)
`Rule::parentCategory(...)`, `Rule::parentTag(...)`, `Rule::parentTerms($taxonomy, ...)` | ✓ |
`Rule::isChild(bool $isChild = true)` | ✓ | ✓
`Rule::userRole(string ...$roles)` | ✓ |
`Rule::userId(int ...$ids)` | ✓ |
`Rule::capability(string ...$capabilities)` | ✓ |
`Rule::editedUserRole(...)`, `Rule::editedUserId(...)` (MB User Meta) | ✓ |
`Rule::custom(callable $callback)`: receives the field group settings, returns a boolean | ✓ |
`Rule::inputValue(string $selector, mixed $value)`: an input of the edit screen has a value, `true` for a checked checkbox | | ✓

Passing a rule to a method that does not support it throws an `InvalidArgumentException`, instead of being ignored. Meta Box cannot combine two rules with the same key with AND, so `Rule::all(Rule::template('a.php'), Rule::template('b.php'))` throws a `LogicException`: use `Rule::template('a.php', 'b.php')`, which matches either template.

---

**Previous:** [Settings pages](settings-pages.md)  
**Next:** [Relationships](relationships.md)
