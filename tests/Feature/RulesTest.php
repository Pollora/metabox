<?php

declare(strict_types=1);

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Rules\Rule;

test('a single rule', function () {
    expect(FieldGroup::make('Homepage', 'homepage')->include(Rule::template('front-page.php'))->build())
        ->toHaveKey('include', ['relation' => 'AND', 'template' => ['front-page.php']]);
});

test('all() combines rules with AND', function () {
    expect(FieldGroup::make('Box', 'box')
        ->include(Rule::all(Rule::userRole('editor', 'author'), Rule::template('landing.php')))
        ->build()['include'])->toBe([
            'relation' => 'AND',
            'user_role' => ['editor', 'author'],
            'template' => ['landing.php'],
        ]);
});

test('any() combines rules with OR', function () {
    expect(FieldGroup::make('Box', 'box')
        ->exclude(Rule::any(Rule::postIds(12, 14), Rule::isChild(), Rule::terms('region', 'europe', 12)))
        ->build()['exclude'])->toBe([
            'relation' => 'OR',
            'ID' => [12, 14],
            'is_child' => true,
            'region' => ['europe', 12],
        ]);
});

test('include and exclude rules', function () {
    $callback = fn (array $metaBox) => true;

    expect(FieldGroup::make('Box', 'box')->include(Rule::any(
        Rule::parentIds(3),
        Rule::slugs('contact', 'about'),
        Rule::category(1, 'news'),
        Rule::tag('fun'),
        Rule::parentCategory('Parent'),
        Rule::parentTag('Parent'),
        Rule::parentTerms('region', 'europe'),
        Rule::userId(1),
        Rule::capability('manage_options'),
        Rule::editedUserRole('subscriber'),
        Rule::editedUserId(7),
        Rule::custom($callback),
    ))->build()['include'])->toBe([
        'relation' => 'OR',
        'parent' => [3],
        'slug' => ['contact', 'about'],
        'category' => [1, 'news'],
        'tag' => ['fun'],
        'parent_category' => ['Parent'],
        'parent_tag' => ['Parent'],
        'parent_region' => ['europe'],
        'user_id' => [1],
        'capability' => ['manage_options'],
        'edited_user_role' => ['subscriber'],
        'edited_user_id' => [7],
        'custom' => $callback,
    ]);
});

test('show and hide rules', function () {
    $config = FieldGroup::make('Video', 'video')
        ->show(Rule::any(Rule::postFormat('video'), Rule::template('video.php'), Rule::terms('genre', 'Documentary')))
        ->hide(Rule::all(Rule::inputValue('#featured', true), Rule::inputValue('#layout', 'wide')))
        ->build();

    expect($config['show'])->toBe([
        'relation' => 'OR',
        'post_format' => ['video'],
        'template' => ['video.php'],
        'genre' => ['Documentary'],
    ])->and($config['hide'])->toBe([
        'relation' => 'AND',
        'input_value' => ['#featured' => true, '#layout' => 'wide'],
    ]);
});

test('rules with the same key merge in any()', function () {
    expect(FieldGroup::make('Box', 'box')
        ->include(Rule::any(Rule::template('a.php'), Rule::template('b.php')))
        ->build()['include'])->toBe(['relation' => 'OR', 'template' => ['a.php', 'b.php']]);
});

test('rules with the same key cannot be combined with AND', function () {
    FieldGroup::make('Box', 'box')->include(Rule::all(Rule::template('a.php'), Rule::template('b.php')));
})->throws(LogicException::class);

test('show() rejects rules MB Show Hide does not support', function () {
    FieldGroup::make('Box', 'box')->show(Rule::userRole('editor'));
})->throws(InvalidArgumentException::class, "The 'user_role' rule is not supported by show(): it only works with include() and exclude().");

test('include() rejects rules MB Include Exclude does not support', function () {
    FieldGroup::make('Box', 'box')->include(Rule::postFormat('video'));
})->throws(InvalidArgumentException::class, "The 'post_format' rule is not supported by include(): it only works with show() and hide().");

test('a group needs at least one rule', function () {
    Rule::any();
})->throws(InvalidArgumentException::class);
