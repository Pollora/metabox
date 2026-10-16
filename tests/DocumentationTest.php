<?php

declare(strict_types=1);

/*
 * Keeps the documentation in sync with the public API: every public method of
 * the fields, meta boxes, blocks, settings pages, locations and rules is
 * documented, and every documented method exists.
 */

/**
 * @return array<string, array<string>> Public method names, with the classes declaring them.
 */
function publicApi(bool $withGetters = false): array
{
    $root = dirname(__DIR__).'/src/';
    $api = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $class = 'Pollora\\Metabox\\'.str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($root)));

        if (! class_exists($class) || (new ReflectionClass($class))->isAbstract()) {
            continue;
        }

        $reflection = new ReflectionClass($class);
        $documented = str_contains($class, '\\Fields\\') || in_array($reflection->getShortName(), ['FieldGroup', 'Block', 'SettingsPage', 'Location', 'Rule', 'Relationship', 'Side', 'MetaboxModel'], true);

        if (! $documented) {
            continue;
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();

            if (str_starts_with($name, '__') || (! $withGetters && str_starts_with($name, 'get')) || in_array($name, ['build', 'buildFieldset', 'valueFor', 'key'], true)) {
                continue;
            }

            $api[$name][] = $reflection->getShortName();
        }
    }

    return $api;
}

function documentation(): string
{
    $root = dirname(__DIR__);

    return implode("\n", array_map('file_get_contents', [...glob($root.'/docs/*.md'), $root.'/README.md']));
}

test('every public method is documented', function () {
    $docs = documentation();
    $undocumented = [];

    foreach (publicApi() as $method => $classes) {
        if (! preg_match('/`(?:[A-Za-z]+::)?'.preg_quote($method, '/').'\(/', $docs)) {
            $undocumented[] = $method.' ('.implode(', ', array_unique($classes)).')';
        }
    }

    expect($undocumented)->toBe([]);
});

test('every documented method exists', function () {
    $api = publicApi(withGetters: true) + ['setting' => true, 'make' => true];
    $unknown = [];

    preg_match_all('/^\s*-\s+\*\*`(?:[A-Za-z]+::)?([A-Za-z_]+)\(/m', documentation(), $listed);
    // Lines starting with $table-> are Laravel migration examples.
    preg_match_all('/->([A-Za-z_]+)\(/', (string) preg_replace('/^\s*\$table->.*$/m', '', documentation()), $called);

    foreach (array_unique([...$listed[1], ...$called[1]]) as $method) {
        if (! isset($api[$method])) {
            $unknown[] = $method;
        }
    }

    expect($unknown)->toBe([]);
});

test('every field is in the field list', function () {
    $list = file_get_contents(dirname(__DIR__).'/docs/field-list.md');
    $missing = [];

    foreach (glob(dirname(__DIR__).'/src/Fields/*.php') as $file) {
        $class = 'Pollora\\Metabox\\Fields\\'.basename($file, '.php');

        if (class_exists($class) && ! (new ReflectionClass($class))->isAbstract() && ! str_contains($list, '[`'.basename($file, '.php').'`]')) {
            $missing[] = basename($file, '.php');
        }
    }

    expect($missing)->toBe([]);
});
