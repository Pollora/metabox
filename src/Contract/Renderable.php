<?php

declare(strict_types=1);

namespace Pollora\Metabox\Contract;

/**
 * Renderable interface defines the contract for any class that can be rendered.
 */
interface Renderable
{
    /**
     * Build the instance and return its data as an array.
     *
     * @return array<mixed> The data of the instance as an array.
     */
    public function build(): array;
}
