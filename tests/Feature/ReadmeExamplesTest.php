<?php

declare(strict_types=1);

use Pollora\Metabox\Block;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Email;
use Pollora\Metabox\Fields\Group;
use Pollora\Metabox\Fields\Number;
use Pollora\Metabox\Fields\Select;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Fields\Url;
use Pollora\Metabox\Location;
use Pollora\Metabox\Rules\Rule;
use Pollora\Metabox\SettingsPage;

// Keeps the Quick start examples of the README working.

test('README meta box example', function () {
    $config = FieldGroup::make('Event', 'event')
        ->location(Location::postTypes('page'))
        ->context('side')
        ->fields([
            Text::make('Venue', 'venue')
                ->placeholder('Where does it take place?')
                ->required(),
            Number::make('Seats', 'seats')
                ->min(1)
                ->step(1),
        ])
        ->build();

    expect($config)->toMatchArray(['id' => 'event', 'context' => 'side', 'post_types' => ['page']])
        ->and($config['fields'][1])->toMatchArray(['type' => 'number', 'min' => 1, 'step' => 1]);
});

test('README block example', function () {
    $config = Block::make('Useful links', 'useful-links')
        ->icon('admin-links')
        ->category('widgets')
        ->renderTemplate('/theme/blocks/useful-links.php')
        ->fields([
            Text::make('Title', 'title'),
            Group::make('Links', 'links')
                ->cloneable()
                ->addButton('Add a link')
                ->fields([
                    Text::make('Label', 'label'),
                    Url::make('URL', 'url'),
                ]),
        ])
        ->build();

    expect($config)->toMatchArray(['type' => 'block', 'category' => 'widgets', 'icon' => 'admin-links'])
        ->and($config['fields'][1]['fields'])->toHaveCount(2);
});

test('README settings page example', function () {
    $page = SettingsPage::make('Site options', 'site-options')
        ->icon('admin-generic')
        ->optionName('site_options')
        ->build();

    $box = FieldGroup::make('Contact', 'contact')
        ->location(Location::settingsPages('site-options'))
        ->fields([
            Email::make('Contact email', 'contact_email'),
        ])
        ->build();

    expect($page)->toMatchArray(['id' => 'site-options', 'icon_url' => 'dashicons-admin-generic', 'option_name' => 'site_options'])
        ->and($box)->toMatchArray(['settings_pages' => ['site-options']]);
});

test('README "Why Metabox" example builds the array shown next to it', function () {
    $linkType = Select::make('Link type', 'link_type')
        ->options(['page' => 'Page', 'custom' => 'Custom URL']);

    $config = FieldGroup::make('Call to action', 'cta')
        ->location(Location::postTypes('page'))
        ->context('side')
        ->include(Rule::template('templates/landing.php'))
        ->fields([
            $linkType,
            Url::make('URL', 'cta_url')->visibleWhen($linkType, 'custom')->required(),
            Number::make('Discount', 'discount')->min(0)->max(100)->columns(6)->tooltip('In percent'),
        ])
        ->build();

    expect($config)->toEqual([
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
    ]);
});
