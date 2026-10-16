<?php

declare(strict_types=1);

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Group;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Location;

$fieldset = FieldGroup::make('Example Fieldset', 'example_fieldset');

beforeEach(function () {
    // Simulate a location object
    $this->location = Location::where('post_types', ['post', 'page']);
    $this->fieldset = FieldGroup::make('Example Fieldset', 'example_fieldset');
});

test('can create a Fieldset instance', function () {
    expect($this->fieldset)->toBeInstanceOf(FieldGroup::class)
        ->and($this->fieldset->build())->toHaveKey('title', 'Example Fieldset')
        ->and($this->fieldset->build())->toHaveKey('id', 'example_fieldset');
});

test('can set context', function () {
    $this->fieldset->context('advanced');
    expect($this->fieldset->build())->toHaveKey('context', 'advanced');
});

test('can set priority', function () {
    $this->fieldset->priority('high');
    expect($this->fieldset->build())->toHaveKey('priority', 'high');
});

test('can configure additional options', function () {
    $this->fieldset->style('seamless')
        ->closed(true)
        ->defaultHidden(true)
        ->autosave(true)
        ->mediaModal(true)
        ->class('custom-class');

    $config = $this->fieldset->build();

    expect($config)->toHaveKey('style', 'seamless')
        ->and($config)->toHaveKey('closed', true)
        ->and($config)->toHaveKey('default_hidden', true)
        ->and($config)->toHaveKey('autosave', true)
        ->and($config)->toHaveKey('media_modal', true)
        ->and($config)->toHaveKey('class', 'custom-class');
});

test('can set location', function () {
    $this->fieldset->location($this->location);
    expect($this->fieldset->build())->toHaveKey('post_types');
});

test('can add groups and fields', function () {
    $mainGroup = FieldGroup::make('Test Fieldset', 'test_fieldset')
        ->fields([
            Text::make('Main Text', 'main_text')
                ->placeholder('Enter main text'),
            Group::make('Sub group', 'sub_group')
                ->fields([
                    Text::make('Sub Text', 'sub_text')
                        ->placeholder('Enter sub text'),
                ]),
        ])
        ->priority('high')
        ->context('side')
        ->location(Location::where('post_types', ['post', 'page']));

    $expectedArray = [
        'title' => 'Test Fieldset',
        'id' => 'test_fieldset',
        'context' => 'side',
        'priority' => 'high',
        'fields' => [
            [
                'type' => 'text',
                'name' => 'Main Text',
                'id' => 'main_text',
                'placeholder' => 'Enter main text',
            ],
            [
                'type' => 'group',
                'name' => 'Sub group',
                'id' => 'sub_group',
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
        'post_types' => ['post', 'page'],
    ];

    expect($mainGroup->build())->toMatchArray($expectedArray);
});
