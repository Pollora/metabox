<?php

declare(strict_types=1);

/*
 * ARCH-07: the harness preset (WordPress classes and functions, the tests), plus what is specific to this package.
 */
$configuration = require __DIR__.'/vendor/pollora/harness/presets/composer-dependency-analyser.php';

return $configuration
    // Meta Box is the plugin this package builds declarations for, not a Composer dependency; its classes are stubbed in stubs/meta-box.php.
    ->ignoreUnknownClassesRegex('~^(WP_|Walker_|Requests_|wpdb$|MB_)~');
