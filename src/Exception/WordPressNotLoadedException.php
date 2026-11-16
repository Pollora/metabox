<?php

declare(strict_types=1);

namespace Pollora\Metabox\Exception;

use Exception;

/**
 * A declaration was built before WordPress was loaded.
 *
 * It extends Exception, as the plain Exception it replaces did, so a caller that catches Exception still does.
 */
final class WordPressNotLoadedException extends Exception
{
    public static function forMetaboxMaker(): self
    {
        return new self('Metabox Maker requires WordPress to be loaded.');
    }
}
