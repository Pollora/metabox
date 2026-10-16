<?php

declare(strict_types=1);

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Group;
use Pollora\Metabox\Fields\Heading;
use Pollora\Metabox\Fields\Tab;
use Pollora\Metabox\Fields\Text;

test('can assign tabs to fields', function () {
    $tabs = FieldGroup::make('Multi-level nested groups', 'main_group')
        ->tabs([
            'contact' => 'Contact',
            'social' => [
                'label' => 'Social Media',
                'icon' => 'dashicons-share',
            ],
        ])
        ->fields([
            Text::make('Main Text', 'main_text')
                ->placeholder('Enter main text')
                ->tab('contact'),
            Group::make('Sub group', 'sub_group')
                ->tab('social')
                ->fields([
                    Text::make('Sub Text', 'sub_text')
                        ->placeholder('Enter sub text'),
                ]),
        ]);

    expect($tabs->build())->toMatchArray([
        'fields' => [
            [
                'type' => 'text',
                'name' => 'Main Text',
                'id' => 'main_text',
                'placeholder' => 'Enter main text',
                'tab' => 'contact',
            ],
            [
                'type' => 'group',
                'name' => 'Sub group',
                'id' => 'sub_group',
                'tab' => 'social',
                'fields' => [
                    [
                        'type' => 'text',
                        'name' => 'Sub Text',
                        'id' => 'sub_text', 'placeholder' => 'Enter sub text'],
                ],
            ],
        ],
        'tabs' => [
            'contact' => 'Contact',
            'social' => [
                'label' => 'Social Media',
                'icon' => 'dashicons-share',
            ],
        ],
        'title' => 'Multi-level nested groups',
        'id' => 'main_group',
        'post_types' => ['post'],
    ]
    );
});

test('can assign group tabs', function () {
    $tabs = FieldGroup::make('Multi-level nested groups', 'main_group')
        ->fields([
            Tab::make('Contact', 'contact')
                ->fields([
                    Text::make('Main Text', 'main_text')
                        ->placeholder('Enter main text'),
                ]),
            Tab::make('Social Media', 'social')
                ->icon('dashicons-share')
                ->fields([
                    Group::make('Sub group', 'sub_group')
                        ->fields([
                            Text::make('Sub Text', 'sub_text')
                                ->placeholder('Enter sub text'),
                        ]),
                ]),
        ]);

    expect($tabs->build())->toMatchArray([
        'fields' => [
            [
                'type' => 'text',
                'name' => 'Main Text',
                'id' => 'main_text',
                'placeholder' => 'Enter main text',
                'tab' => 'contact',
            ],
            [
                'type' => 'group',
                'name' => 'Sub group',
                'id' => 'sub_group',
                'tab' => 'social',
                'fields' => [
                    [
                        'type' => 'text',
                        'name' => 'Sub Text',
                        'id' => 'sub_text',
                        'placeholder' => 'Enter sub text',
                    ],
                ],
            ],
        ],
        'tabs' => [
            'contact' => [
                'label' => 'Contact',
            ],
            'social' => [
                'label' => 'Social Media',
                'icon' => 'dashicons-share',
            ],
        ],
        'title' => 'Multi-level nested groups',
        'id' => 'main_group',
        'post_types' => ['post'],
    ]
    );
});

test('tab accepts layout fields and assigns them to the tab', function () {
    $config = FieldGroup::make('Box', 'box')->fields([
        Tab::make('General', 'general')->fields([
            Heading::make('Section'),
            Text::make('Label', 'label'),
        ]),
    ])->build();

    expect($config['fields'][0])->toMatchArray(['type' => 'heading', 'tab' => 'general'])
        ->and($config['fields'][1])->toMatchArray(['type' => 'text', 'tab' => 'general']);
});
