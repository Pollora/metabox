<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

use LogicException;
use Pollora\Metabox\Enums\TooltipPosition;

/**
 * Trait for showing a tooltip next to the field input (Meta Box Tooltip).
 */
trait InputTooltip
{
    /**
     * The input tooltip content, or its full settings.
     *
     * @var string|array<mixed>
     */
    protected string|array $tooltip_input;

    /**
     * Show a tooltip next to the field input, with the same arguments as tooltip().
     *
     * @param  string  $content  The tooltip content.
     * @param  string|null  $icon  'info' (default), 'help', a Dashicons name or an image URL.
     * @param  string|TooltipPosition|null  $position  'top' (default), 'bottom', 'left' or 'right'.
     * @param  bool  $allowHtml  Whether the content is rendered as HTML.
     */
    public function inputTooltip(string $content, ?string $icon = null, string|TooltipPosition|null $position = null, bool $allowHtml = false): static
    {
        self::ensureInputTooltipSupported($this->type);

        $this->tooltip_input = $this->tooltipSettings($content, $icon, $position, $allowHtml);

        return $this;
    }

    /**
     * Meta Box Tooltip only adds input tooltips to these field types.
     */
    protected static function ensureInputTooltipSupported(string $type): void
    {
        if (! in_array($type, ['date', 'datetime', 'email', 'number', 'password', 'text', 'time', 'url', 'select', 'select_advanced'], true)) {
            throw new LogicException("Meta Box Tooltip does not support input tooltips on {$type} fields.");
        }
    }
}
