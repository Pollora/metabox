<?php

declare(strict_types=1);

use Pollora\Metabox\Block;
use Pollora\Metabox\Enums\BlockMode;
use Pollora\Metabox\Enums\InputTextType;
use Pollora\Metabox\Enums\TabStyle;
use Pollora\Metabox\Enums\ToolbarPosition;
use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\BlockEditor;
use Pollora\Metabox\Fields\Link;
use Pollora\Metabox\Fields\Text;

test('block editor field', function () {
    expect(BlockEditor::make('Content', 'content')
        ->allowedBlocks(['core/heading', 'core/image'])
        ->height('500px')
        ->toolbarPosition(ToolbarPosition::Contextual)
        ->build())->toMatchArray([
            'type' => 'block_editor',
            'allowed_blocks' => ['core/heading', 'core/image'],
            'height' => '500px',
            'toolbar_position' => 'contextual',
        ]);
});

test('block editor rejects an invalid toolbar position', function () {
    BlockEditor::make('Content', 'content')->toolbarPosition('bottom');
})->throws(InvalidArgumentException::class);

test('link field', function () {
    expect(Link::make('Link', 'link')->build())->toMatchArray(['type' => 'link', 'id' => 'link']);
});

test('text field accepts the other HTML5 input types', function (string $type) {
    expect(Text::make('Field', 'field')->type($type)->build())->toHaveKey('type', $type);
})->with(['search', 'tel', 'month', 'week', 'datetime-local']);

test('text field accepts HTML5 input type enums', function () {
    expect(Text::make('Phone', 'phone')->type(InputTextType::Tel)->build())->toHaveKey('type', 'tel');
});

test('meta box tab settings', function () {
    expect(FieldGroup::make('Box', 'box')
        ->tabStyle(TabStyle::BOX)
        ->tabWrapper(false)
        ->tabDefaultActive('general')
        ->tabRemember()
        ->build())->toMatchArray([
            'tab_style' => 'box',
            'tab_wrapper' => false,
            'tab_default_active' => 'general',
            'tab_remember' => true,
        ]);
});

test('meta box validation, revision and custom table', function () {
    $config = FieldGroup::make('Box', 'box')
        ->validation(
            ['email' => ['required' => true, 'minlength' => 7]],
            ['email' => ['required' => 'Email is required']],
        )
        ->revision()
        ->customTable('transactions')
        ->build();

    expect($config)->toMatchArray([
        'validation' => [
            'rules' => ['email' => ['required' => true, 'minlength' => 7]],
            'messages' => ['email' => ['required' => 'Email is required']],
        ],
        'revision' => true,
        'storage_type' => 'custom_table',
        'table' => 'wp_transactions',
    ]);
});

test('validation without messages omits the messages key', function () {
    expect(FieldGroup::make('Box', 'box')->validation(['email' => ['required' => true]])->build()['validation'])
        ->toBe(['rules' => ['email' => ['required' => true]]]);
});

test('block mode accepts edit and preview', function () {
    expect(Block::make('Hero', 'hero')->mode(BlockMode::Preview)->build())->toHaveKey('mode', 'preview')
        ->and(Block::make('Hero', 'hero')->mode('edit')->build())->toHaveKey('mode', 'edit');
});

test('block mode rejects values Meta Box does not support', function () {
    Block::make('Hero', 'hero')->mode('auto');
})->throws(InvalidArgumentException::class);

test('block storage type is still available', function () {
    expect(Block::make('Hero', 'hero')->storageType('post_meta')->build())->toHaveKey('storage_type', 'post_meta');
});
