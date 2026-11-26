<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

/**
 * Trait for clonable fields.
 */
trait Clonable
{
    /**
     * @var bool Defaults to false.
     */
    protected bool $clone;

    /**
     * @var bool Defaults to false.
     */
    protected bool $clone_default;

    /**
     * @var bool Defaults to false.
     */
    protected bool $clone_as_multiple;

    /**
     * @var int Defaults to null.
     */
    protected int|string $max_clone;

    /**
     * @var int Defaults to null.
     */
    protected int $min_clone;

    /**
     * @var string Defaults to null.
     */
    protected string $add_button;

    /**
     * Whether the field starts without any clone, only with the add button.
     */
    protected bool $clone_empty_start;

    /**
     * Indicates whether the field can be cloned.
     *
     * @param  bool  $clone  Defaults to true.
     */
    public function cloneable(?bool $clone = true): static
    {
        $this->clone = (bool) $clone;

        return $this;
    }

    /**
     * Indicates whether the field should clone its default values.
     *
     * @param  bool  $cloneDefault  Defaults to true.
     */
    public function cloneDefaults(bool $cloneDefault = true): static
    {
        $this->clone_default = $cloneDefault;

        return $this;
    }

    /**
     * Indicates whether the field should clone as multiple instances.
     *
     * @param  bool  $cloneAsMultiple  Defaults to true.
     */
    public function cloneAsMultiple(bool $cloneAsMultiple = true): static
    {
        $this->clone_as_multiple = $cloneAsMultiple;

        return $this;
    }

    /**
     * Sets the maximum number of clones allowed.
     */
    public function maxClone(int|string $maxClone): static
    {
        $this->max_clone = $maxClone;

        return $this;
    }

    /**
     * Sets the minimum number of clones allowed.
     */
    public function minClone(int $minClone): static
    {
        $this->min_clone = $minClone;

        return $this;
    }

    /**
     * Sets the text for the clone button.
     */
    public function addButton(string $addButton): static
    {
        $this->add_button = $addButton;

        return $this;
    }

    /**
     * Start without any clone: only the add button is displayed.
     */
    public function cloneEmptyStart(bool $cloneEmptyStart = true): static
    {
        $this->clone_empty_start = $cloneEmptyStart;

        return $this;
    }
}
