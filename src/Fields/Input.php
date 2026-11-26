<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields;

use Pollora\Metabox\Fields\Settings\AutocompleteAttribute;
use Pollora\Metabox\Fields\Settings\GeoBinding;
use Pollora\Metabox\Fields\Settings\TextLength;

/**
 * Base class for the fields rendered as an HTML input: text, email, url, number, range and password.
 */
abstract class Input extends Field
{
    use AutocompleteAttribute;
    use GeoBinding;
    use TextLength;

    /**
     * The size of the input field.
     */
    protected int $size;

    /**
     * The text to prepend to the input field.
     */
    protected string $prepend;

    /**
     * The text to append to the input field.
     */
    protected string $append;

    /**
     * The datalist options for the input field.
     *
     * @var array<mixed>
     */
    protected array $datalist;

    /**
     * The regular expression the value must match.
     */
    protected string $pattern;

    /**
     * Set the size of the input field.
     *
     * @param  int  $size  The size of the input field.
     * @return static Returns the instance of the Text class for method chaining.
     */
    public function size(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Set the text to prepend to the input field.
     *
     * @param  string  $text  The text to prepend.
     * @return static Returns the instance of the Text class for method chaining.
     */
    public function prepend(string $text): static
    {
        $this->prepend = $text;

        return $this;
    }

    /**
     * Set the text to append to the input field.
     *
     * @param  string  $text  The text to append.
     * @return static Returns the instance of the Text class for method chaining.
     */
    public function append(string $text): static
    {
        $this->append = $text;

        return $this;
    }

    /**
     * Set the datalist options for the input field.
     *
     * @param  string  $id  The ID of the datalist.
     * @param  array<mixed>  $options  The options for the datalist.
     * @return static Returns the instance of the Text class for method chaining.
     */
    public function datalist(string $id, array $options): static
    {
        $this->datalist = [
            'id' => $id,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Set the regular expression the value must match, checked by the browser, e.g. '[0-9]{5}'.
     */
    public function pattern(string $pattern): static
    {
        $this->pattern = $pattern;

        return $this;
    }
}
