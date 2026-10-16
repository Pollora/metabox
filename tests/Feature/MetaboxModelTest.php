<?php

declare(strict_types=1);

use Pollora\Metabox\Block;
use Pollora\Metabox\Enums\ModelSupport;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Location;
use Pollora\Metabox\MetaboxModel;

class EventDetailsModel
{
    public function getTable(): string
    {
        return 'event_details';
    }
}

afterEach(function () {
    unset($GLOBALS['test_did_actions'], $GLOBALS['test_doing_actions'], $GLOBALS['test_actions']);
});

describe('custom models', function () {
    test('build their MB Custom Table settings', function () {
        $model = MetaboxModel::make('transaction')
            ->table('transactions')
            ->labels(plural: 'Transactions', singular: 'Transaction', addNewItem: 'New transaction')
            ->menuIcon('dashicons-money-alt')
            ->menuPosition(25)
            ->capability('manage_options')
            ->supports(ModelSupport::Author, 'published_date', ModelSupport::Author);

        expect($model->build())->toBe([
            'table' => 'wp_transactions',
            'labels' => ['name' => 'Transactions', 'singular_name' => 'Transaction', 'add_new_item' => 'New transaction'],
            'menu_position' => 25,
            'menu_icon' => 'dashicons-money-alt',
            'capability' => 'manage_options',
            'supports' => ['author', 'published_date'],
        ])->and($GLOBALS['test_actions']['init'])->toBe([10]);
    });

    test('can be hidden from the menu or shown as a submenu', function () {
        expect(MetaboxModel::make('log')->table('logs')->showInMenu(false)->build())->toBe(['table' => 'wp_logs', 'show_in_menu' => false])
            ->and(MetaboxModel::make('invoice')->table('invoices')->parent('tools.php')->build())->toHaveKey('parent', 'tools.php');
    });

    test('take the table of a class, or an unprefixed table', function () {
        expect(MetaboxModel::make('event_detail')->table(EventDetailsModel::class)->getTable())->toBe('wp_event_details')
            ->and(MetaboxModel::make('legacy')->table('legacy_items', prefix: false)->getTable())->toBe('legacy_items');
    });

    test('are validated', function () {
        expect(fn () => MetaboxModel::make('no_table')->build())->toThrow(LogicException::class)
            ->and(fn () => MetaboxModel::make('bad_table')->table('my-table'))->toThrow(InvalidArgumentException::class)
            ->and(fn () => MetaboxModel::make('bad_support')->supports('comments'))->toThrow(InvalidArgumentException::class);

        MetaboxModel::make('twice');
        expect(fn () => MetaboxModel::make('twice'))->toThrow(LogicException::class);
    });

    test('must be declared before init', function () {
        $GLOBALS['test_did_actions'] = ['init'];

        expect(fn () => MetaboxModel::make('too_late'))->toThrow(LogicException::class);
    });
});

describe('custom tables', function () {
    test('are prefixed', function () {
        expect(FieldGroup::make('Event', 'event')->customTable('events')->build())->toMatchArray(['storage_type' => 'custom_table', 'table' => 'wp_events'])
            ->and(FieldGroup::make('Event', 'event')->customTable('events', prefix: false)->build()['table'])->toBe('events')
            ->and(Block::make('Hero', 'hero')->customTable(EventDetailsModel::class)->build()['table'])->toBe('wp_event_details');
    });

    test('are taken from the models of the location', function () {
        MetaboxModel::make('order')->table('orders');
        MetaboxModel::make('refund')->table('orders');
        MetaboxModel::make('ticket')->table('tickets');

        expect(FieldGroup::make('Order', 'order_details')->location(Location::models(['order', 'refund']))->build())
            ->toMatchArray(['storage_type' => 'custom_table', 'table' => 'wp_orders', 'models' => ['order', 'refund']])
            ->and(fn () => FieldGroup::make('Mixed', 'mixed')->location(Location::models(['order', 'ticket']))->build())->toThrow(LogicException::class)
            ->and(fn () => FieldGroup::make('Unknown', 'unknown')->location(Location::models('unknown'))->build())->toThrow(LogicException::class)
            ->and(FieldGroup::make('Unknown', 'unknown')->location(Location::models('unknown'))->customTable('unknowns')->build()['table'])->toBe('wp_unknowns');
    });
});
