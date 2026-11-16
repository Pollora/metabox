<?php

declare(strict_types=1);

use Pollora\Metabox\Fields\Settings\TimepickerOptions;
use Pollora\Metabox\Fields\Timepicker;
use Pollora\Metabox\Fields\Wysiwyg;

dataset('wysiwyg setters', [
    'wpautop' => ['wpautop', false, 'wpautop'],
    'mediaButtons' => ['mediaButtons', true, 'media_buttons'],
    'defaultEditor' => ['defaultEditor', 'html', 'default_editor'],
    'dragDropUpload' => ['dragDropUpload', true, 'drag_drop_upload'],
    'textareaName' => ['textareaName', 'body', 'textarea_name'],
    'textareaRows' => ['textareaRows', 12, 'textarea_rows'],
    'tabindex' => ['tabindex', 3, 'tabindex'],
    'tabfocusElements' => ['tabfocusElements', ':prev,:next', 'tabfocus_elements'],
    'editorCss' => ['editorCss', 'body{color:red}', 'editor_css'],
    'editorClass' => ['editorClass', 'my-editor', 'editor_class'],
    'teeny' => ['teeny', true, 'teeny'],
    'tinymce' => ['tinymce', ['toolbar1' => 'bold'], 'tinymce'],
    'quicktags' => ['quicktags', ['buttons' => 'strong'], 'quicktags'],
    'distractionFreeWriting' => ['distractionFreeWriting', false, 'dfw'],
]);

test('each wysiwyg editor setter stores its value under the WordPress editor option', function (string $method, mixed $value, string $key) {
    $field = Wysiwyg::make('Body', 'body');

    expect($field->{$method}($value))->toBe($field)
        ->and($field->build()['options'])->toBe([$key => $value]);
})->with('wysiwyg setters');

test('distraction free writing is enabled by default', function () {
    expect(Wysiwyg::make('Body', 'body')->distractionFreeWriting()->build()['options'])->toBe(['dfw' => true]);
});

/**
 * @return array<string, array{string, mixed}>
 */
function timepickerSetters(): array
{
    $samples = ['int' => 4, 'string' => 'sample', 'bool' => false, 'array' => ['a', 'b']];
    $setters = [];

    foreach ((new ReflectionClass(TimepickerOptions::class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (! str_ends_with((string) $method->getFileName(), 'TimepickerOptions.php')) {
            continue;
        }

        $parameters = $method->getParameters();
        if (count($parameters) !== 1 || (string) $method->getReturnType() !== 'static' || ! isset($samples[(string) $parameters[0]->getType()])) {
            continue;
        }

        $setters[$method->getName()] = [$method->getName(), $samples[(string) $parameters[0]->getType()]];
    }

    return $setters;
}

test('the timepicker option setters are discovered', function () {
    expect(count(timepickerSetters()))->toBeGreaterThan(50);
});

test('each timepicker setter stores its value in js_options under its own name', function (string $method, mixed $value) {
    // The one setter whose jQuery UI option has another name than the method.
    $option = $method === 'defaultTimeValue' ? 'defaultValue' : $method;
    $field = Timepicker::make('Time', 'time');

    expect($field->{$method}($value))->toBe($field)
        ->and($field->build()['js_options'])->toBe([$option => $value]);
})->with(fn () => timepickerSetters());

test('timepicker boolean toggles default to true', function () {
    $built = Timepicker::make('Time', 'time')->showHour()->oneLine()->timeOnly()->isRTL()->build();

    expect($built['js_options'])->toBe(['showHour' => true, 'oneLine' => true, 'timeOnly' => true, 'isRTL' => true]);
});
