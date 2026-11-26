<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

use Pollora\Metabox\Enums\TooltipPosition;
use Pollora\Metabox\Validation\OptionValidation;

/**
 * Trait for showing a tooltip next to the field label (Meta Box Tooltip).
 */
trait Tooltip
{
    /**
     * The tooltip content, or its full settings.
     *
     * @var string|array<mixed>
     */
    protected string|array $tooltip;

    /**
     * Show a tooltip next to the field label.
     *
     * @param  string  $content  The tooltip content.
     * @param  string|null  $icon  'info' (default), 'help', a Dashicons name or an image URL.
     * @param  string|TooltipPosition|null  $position  'top' (default), 'bottom', 'left' or 'right'.
     * @param  bool  $allowHtml  Whether the content is rendered as HTML.
     */
    public function tooltip(string $content, ?string $icon = null, string|TooltipPosition|null $position = null, bool $allowHtml = false): static
    {
        $this->tooltip = $this->tooltipSettings($content, $icon, $position, $allowHtml);

        return $this;
    }

    /**
     * The tooltip settings: the content alone when no other setting is given.
     *
     * @return string|array<mixed>
     */
    protected function tooltipSettings(string $content, ?string $icon, string|TooltipPosition|null $position, bool $allowHtml): string|array
    {
        $settings = array_filter([
            'content' => $content,
            'icon' => $icon,
            'position' => $position === null ? null : OptionValidation::check($position, TooltipPosition::class),
            'allow_html' => $allowHtml ?: null,
        ], fn ($value) => $value !== null);

        return count($settings) === 1 ? $content : $settings;
    }
}
