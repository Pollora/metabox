<?php

declare(strict_types=1);

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\Fields\Email;
use Pollora\Metabox\Fields\Input;
use Pollora\Metabox\Fields\Number;
use Pollora\Metabox\Fields\Password;
use Pollora\Metabox\Fields\Range;
use Pollora\Metabox\Fields\Text;
use Pollora\Metabox\Fields\Url;
use Pollora\Metabox\SettingsPage;

test('input fields share the input settings', function (string $class) {
    $config = $class::make('Field', 'field')
        ->size(20)
        ->prepend('@')
        ->append('.com')
        ->autocomplete('off')
        ->minLength(2)
        ->maxLength(20)
        ->pattern('[a-z]+')
        ->build();

    expect($config)->toMatchArray([
        'size' => 20,
        'prepend' => '@',
        'append' => '.com',
        'autocomplete' => 'off',
        'minlength' => 2,
        'maxlength' => 20,
        'pattern' => '[a-z]+',
    ]);
})->with([Text::class, Email::class, Url::class, Number::class, Range::class, Password::class]);

test('only Text can change its input type', function () {
    expect(is_subclass_of(Email::class, Input::class))->toBeTrue()
        ->and(method_exists(Number::class, 'type'))->toBeFalse()
        ->and(method_exists(Email::class, 'type'))->toBeFalse()
        ->and(method_exists(Text::class, 'type'))->toBeTrue();
});

test('make() accepts named arguments', function () {
    expect(Text::make(name: 'Title', id: 'title')->build())->toMatchArray(['name' => 'Title', 'id' => 'title'])
        ->and(FieldGroup::make(title: 'Event', id: 'event')->build())->toMatchArray(['title' => 'Event', 'id' => 'event'])
        ->and(SettingsPage::make(pageTitle: 'Options', id: 'options')->build())->toMatchArray(['page_title' => 'Options', 'id' => 'options']);
});
