<?php

declare(strict_types=1);

namespace Pollora\Metabox\Attributes\Contracts;

use Pollora\Metabox\FieldGroup;
use Pollora\Metabox\SettingsPage;

/**
 * Connects the declared classes to a framework: how they are instantiated, the defaults of
 * the site, the classes used as targets and how block views are rendered.
 */
interface Integration
{
    /**
     * Create the instance of a declared class.
     *
     * @param  class-string  $class
     */
    public function instance(string $class): object;

    /**
     * Apply the defaults of the site, before the attributes of the class.
     *
     * @param  class-string  $class
     */
    public function configure(FieldGroup|SettingsPage $builder, string $class): void;

    /**
     * The slug of a class used as a target, e.g. the post type a class declares,
     * or null when the class declares nothing for this location key.
     *
     * @param  class-string  $class
     */
    public function slug(string $class, string $key): ?string;

    /**
     * The view rendering a block without a #[View] attribute, or null to render nothing.
     *
     * @param  class-string  $class
     */
    public function view(string $class, string $blockId): ?string;

    /**
     * Render a block view.
     *
     * @param  array<string, mixed>  $data
     */
    public function render(string $view, array $data): string;
}
