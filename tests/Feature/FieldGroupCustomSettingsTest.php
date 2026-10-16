<?php

declare(strict_types=1);

use Pollora\Metabox\Enums\Context;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Location;

test('can add custom settings to a metabox', function () {
    $metabox = FieldGroup::make('Custom Settings Test', 'custom_settings_test')
        ->description('Test metabox with custom settings')
        ->priority('high')
        ->setting('custom_option', 'custom_value')
        ->setting('enable_ajax', true)
        ->setting('data_source', '/api/data')
        ->location(Location::where('post_types', 'post'));

    $data = $metabox->build();

    expect($data)->toMatchArray([
        'title' => 'Custom Settings Test',
        'id' => 'custom_settings_test',
        'description' => 'Test metabox with custom settings',
        'priority' => 'high',
        'custom_option' => 'custom_value',
        'enable_ajax' => true,
        'data_source' => '/api/data',
        'post_types' => 'post',
    ]);
});

$metabox = FieldGroup::make('Field group with fields', 'field_group_with_fields')
    ->fields([
        Text::make('Name', 'name')->required(),
        Text::make('Email', 'email')->type('email'),
    ])
    ->setting('validation_endpoint', '/api/validate')
    ->setting('autosave_interval', 30)
    ->location(Location::where('post_types', 'page'));

test('custom settings work with fields', function () {
    $metabox = FieldGroup::make('Field group with fields', 'field_group_with_fields')
        ->fields([
            Text::make('Name', 'name')->required(),
            Text::make('Email', 'email')->type('email'),
        ])
        ->setting('validation_endpoint', '/api/validate')
        ->setting('autosave_interval', 30)
        ->location(Location::where('post_types', 'page'));

    $data = $metabox->build();

    expect($data)->toMatchArray([
        'title' => 'Field group with fields',
        'id' => 'field_group_with_fields',
        'validation_endpoint' => '/api/validate',
        'autosave_interval' => 30,
        'post_types' => 'page',
    ]);

    expect($data['fields'])->toHaveCount(2);
    expect($data['fields'][0])->toMatchArray([
        'name' => 'Name',
        'id' => 'name',
        'type' => 'text',
        'required' => true,
    ]);
});

test('empty custom settings are not included in metabox build output', function () {
    $metabox = FieldGroup::make('No Custom Settings', 'no_custom_settings')
        ->description('Test without custom settings')
        ->location(Location::where('post_types', 'post'));

    $data = $metabox->build();

    expect($data)->not->toHaveKey('settings');
    expect($data)->toMatchArray([
        'title' => 'No Custom Settings',
        'id' => 'no_custom_settings',
        'description' => 'Test without custom settings',
        'post_types' => 'post',
    ]);
});

test('custom settings can override existing metabox properties', function () {
    $metabox = FieldGroup::make('override_test', 'Override Test')
        ->description('Original description')
        ->setting('description', 'Overridden description')
        ->location(Location::where('post_types', 'post'));

    $data = $metabox->build();

    expect($data['description'])->toBe('Overridden description');
});

test('getSettings method returns all custom settings', function () {
    $metabox = FieldGroup::make('get_settings_test', 'Get Settings Test')
        ->setting('option1', 'value1')
        ->setting('option2', 'value2')
        ->setting('option3', true);

    $settings = $metabox->getSettings();

    expect($settings)->toBe([
        'option1' => 'value1',
        'option2' => 'value2',
        'option3' => true,
    ]);
});

it('uses valid Meta Box values for the context enum', function () {
    expect(Context::Side->value)->toBe('side')
        ->and(Context::FormTop->value)->toBe('form_top');
});

it('accepts a null class', function () {
    expect(FieldGroup::make('Box', 'box')->class(null)->build())->not->toHaveKey('class');
});

it('targets posts with the post_types key by default', function () {
    expect(FieldGroup::make('Box', 'box')->build())->toHaveKey('post_types', ['post'])
        ->not->toHaveKey('post_type');
});
