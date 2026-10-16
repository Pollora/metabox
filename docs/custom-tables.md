# Custom tables

[MB Custom Table](https://docs.metabox.io/extensions/mb-custom-table/) stores field values in a table of their own, with one column per field, instead of the meta tables. It also provides custom models: data stored only in a custom table and managed from its own admin screens, like a post type.

## Table names

Tables are named **without the WordPress table prefix**, which the package adds: `events` is the table `wp_events` on a site whose prefix is `wp_`. In a Pollora application, WordPress takes its prefix from the Laravel database connection, so `customTable('events')` is the table created by `Schema::create('events')`.

On a multisite network, the prefix is the one of the current site, e.g. `wp_2_events`: each site needs its own table.

A table name can only contain letters, digits and underscores. To use an existing table without the prefix, pass `prefix: false`.

## Storing fields in a custom table

```php
<?php

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Location;

FieldGroup::make('Event details', 'event_details')
    ->location(Location::postTypes('event'))
    ->customTable('events')
    ->fields([
        Text::make('Venue', 'venue'),
        Number::make('Price', 'price'),
    ]);
```

Each row holds the values of one post, term or user: the `ID` column is its ID, and the other columns are named after the field IDs. A group is stored in a single column, named after the group ID.

- **`customTable(string $table, bool $prefix = true)`**: Stores the field values in the table. `$table` can also be a class with a `getTable()` method, such as an Eloquent model.

## Custom models

```php
<?php

use Pollora\Metabox\Enums\ModelSupport;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Location;
use Pollora\Metabox\MetaboxModel;

MetaboxModel::make('transaction')
    ->table('transactions')
    ->labels(plural: 'Transactions', singular: 'Transaction')
    ->menuIcon('dashicons-money-alt')
    ->supports(ModelSupport::Author, ModelSupport::PublishedDate);

FieldGroup::make('Transaction', 'transaction_details')
    ->location(Location::models('transaction'))
    ->fields([
        Number::make('Amount', 'amount'),
        Select::make('Status', 'status')->options(['pending' => 'Pending', 'completed' => 'Completed']),
    ]);
```

The model is registered on the `init` action: declare it before, for instance in a service provider or in `functions.php`. Declaring it later, or declaring a name twice, throws a `LogicException`.

A field group located on a model stores its values in the table of the model: `customTable()` is not needed. A model not declared with `MetaboxModel`, or models with different tables in the same field group, throw a `LogicException`.

### Methods

- **`MetaboxModel::make(string $name)`**: Creates the model. The name is used in the admin URLs and by `Location::models()`.
- **`table(string $table, bool $prefix = true)`**: Sets the table of the model. Required. `$table` can also be a class with a `getTable()` method, such as an Eloquent model.
- **`labels(string $plural, string $singular, ...)`**: Sets the labels. The other labels are named arguments: `menuName`, `allItems`, `addNew`, `addNewItem`, `editItem`, `searchItems`, `notFound`, `itemAdded`, `itemUpdated` and `itemDeleted`. Without them, MB Custom Table uses generic translated labels, such as "Add New Item".
- **`menuIcon(string $icon)`**: Sets the menu icon: a Dashicons class, an image URL or a base64-encoded SVG data URI.
- **`menuPosition(int $position)`**: Sets the position of the menu.
- **`parent(string $parent)`**: Shows the menu as a submenu of the given menu, e.g. `tools.php`.
- **`showInMenu(bool $show = true)`**: Set to `false` to remove the menu.
- **`capability(string $capability)`**: Sets the capability required to manage the items. Defaults to `edit_posts`.
- **`supports(string|ModelSupport ...$supports)`**: Fills the `author`, `published_date` or `modified_date` column automatically. Another value throws an exception.
- **`MetaboxModel::get(string $name)`**: Returns the model declared with this name, or `null`.
- **`getTable()`**: Returns the full table name, with the prefix.

## Creating the tables

The package does not create tables: create them with a migration, before the field groups save values.

### With Laravel migrations (Pollora)

In a Pollora application, [pollora/metabox-bridge](https://github.com/Pollora/metabox-bridge) adds the `metaboxObject()` and `metaboxModel()` migration methods, which create the `ID` column and the columns of the model supports. It also provides Eloquent base models that unserialize the values stored by Meta Box and clear its cache when a row is saved. Without it, write the columns yourself:

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Values of posts, terms or users: the ID is theirs
Schema::create('events', function (Blueprint $table) {
    $table->unsignedBigInteger('ID')->primary();
    $table->string('venue', 100)->nullable()->index();
    $table->decimal('price', 10, 2)->nullable();
});

// Custom model: the ID is auto-incremented, with the columns of its supports
Schema::create('transactions', function (Blueprint $table) {
    $table->bigIncrements('ID');
    $table->unsignedBigInteger('author')->nullable()->index();
    $table->dateTime('published_date')->nullable();
    $table->decimal('amount', 10, 2)->nullable();
    $table->string('status', 20)->nullable()->index();
});
```

Make the columns nullable: a field without value may be missing from a row. Cloneable fields, multiple fields and groups are stored serialized: use a `text` column.

### Without Laravel

Use the MB Custom Table API, on activation of your plugin or on `init`. It does not add the prefix, and creates the `ID` column and the columns of the model supports itself:

```php
add_action('init', function () {
    global $wpdb;

    MetaBox\CustomTable\API::create("{$wpdb->prefix}events", [
        'venue' => 'VARCHAR(100)',
        'price' => 'DECIMAL(10,2)',
    ], ['venue']);
}, 20);
```

For a model, call it after the model is registered (the code above runs at priority 20, after `MetaboxModel`), so that the `ID` column is auto-incremented.

## Reading values

With [pollora/metabox-bridge](https://github.com/Pollora/metabox-bridge), read and write the rows with Eloquent. Otherwise, use the Meta Box functions:

```php
// Formatted value, cached for the request
$venue = rwmb_meta('venue', ['storage_type' => 'custom_table', 'table' => $wpdb->prefix.'events'], $postId);

// Value of a model item
$status = rwmb_meta('status', ['object_type' => 'model', 'type' => 'transaction'], $transactionId);

// Raw value
$status = MetaBox\CustomTable\API::get_value('status', $transactionId, MetaboxModel::get('transaction')->getTable());
```

---

**Previous:** [Relationships](relationships.md)
