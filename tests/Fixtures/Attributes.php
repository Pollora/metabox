<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use Attribute;
use Pollora\Metabox\Attributes\Block;
use Pollora\Metabox\Attributes\Block\Category;
use Pollora\Metabox\Attributes\Block\Description;
use Pollora\Metabox\Attributes\Block\Icon;
use Pollora\Metabox\Attributes\Block\Mode;
use Pollora\Metabox\Attributes\Block\View;
use Pollora\Metabox\Attributes\Contracts\BlockSetting;
use Pollora\Metabox\Attributes\Contracts\FieldGroupSetting;
use Pollora\Metabox\Attributes\Contracts\SettingsPageSetting;
use Pollora\Metabox\Attributes\FieldGroup;
use Pollora\Metabox\Attributes\FieldGroup\PostTypes;
use Pollora\Metabox\Attributes\FieldGroup\SettingsPages;
use Pollora\Metabox\Attributes\FieldGroup\Taxonomies;
use Pollora\Metabox\Attributes\SettingsPage;
use Pollora\Metabox\Attributes\SettingsPage\OptionName;
use Pollora\Metabox\Attributes\SettingsPage\Position;
use Pollora\Metabox\Attributes\SettingsPage\SubmitButton;
use Pollora\Metabox\Enums\BlockMode;
use Pollora\Metabox\Fields\Color;
use Pollora\Metabox\Fields\Text;

#[FieldGroup('Solution fields')]
#[PostTypes('solution', 'page')]
#[Taxonomies('category')]
final class SolutionFields
{
    public static int $instances = 0;

    public function __construct()
    {
        self::$instances++;
    }

    public function fields(): array
    {
        return [Color::make('Color', 'color')];
    }

    public function configuring(\Pollora\Metabox\FieldGroup $group): void
    {
        $group->priority('high');
    }
}

#[Block('Quote', 'quote')]
#[Icon('format-quote')]
#[Description('A quote with its author')]
#[Category('theme')]
#[Mode(BlockMode::Preview)]
#[View('blocks.quote')]
final class Quote
{
    public function fields(): array
    {
        return [Text::make('Quote', 'quote')];
    }
}

#[SettingsPage('Theme options', 'theme-options')]
#[Position(2)]
#[OptionName('theme')]
#[SubmitButton('Save')]
final class ThemeOptions {}

#[FieldGroup('Footer', 'footer')]
#[SettingsPages(ThemeOptions::class)]
#[PostTypes(Event::class)]
final class FooterOptions
{
    public function fields(): array
    {
        return [Text::make('Copyright', 'copyright')];
    }
}

/**
 * A post type declared by a framework, resolved by the integration.
 */
final class Event {}

#[FieldGroup('Plain')]
final class Plain
{
    public function fields(): array
    {
        return [];
    }
}

#[FieldGroup('Duplicate', 'solution_fields')]
final class DuplicateId
{
    public function fields(): array
    {
        return [];
    }
}

#[FieldGroup('No fields')]
final class WithoutFields {}

#[FieldGroup('Two', 'two')]
#[Block('Two', 'two')]
final class TwoDeclarations
{
    public function fields(): array
    {
        return [];
    }
}

#[FieldGroup('Iconic', 'iconic')]
#[Icon('star')]
final class WrongSetting
{
    public function fields(): array
    {
        return [];
    }
}

#[FieldGroup('Unknown target', 'unknown_target')]
#[PostTypes(Plain::class)]
final class UnresolvedTarget
{
    public function fields(): array
    {
        return [];
    }
}

#[Block('Unrendered', 'unrendered')]
#[View('blocks.unrendered')]
final class ViewWithoutIntegration
{
    public function fields(): array
    {
        return [];
    }
}

#[Block('Plain block', 'plain-block')]
final class PlainBlock
{
    public function fields(): array
    {
        return [];
    }
}

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Closed implements FieldGroupSetting
{
    public function configure(\Pollora\Metabox\FieldGroup $group): void
    {
        $group->closed(true);
    }
}

#[FieldGroup('Custom setting', 'custom_setting')]
#[Closed]
final class CustomSetting
{
    public function fields(): array
    {
        return [];
    }
}

#[SettingsPage('Fields page', 'fields-page')]
final class SettingsPageWithFields
{
    public function fields(): array
    {
        return [];
    }
}

#[Block('Template block', 'template-block')]
final class TemplateBlock
{
    public function fields(): array
    {
        return [];
    }

    public function configuring(\Pollora\Metabox\Block $block): void
    {
        $block->renderTemplate('blocks/template.php');
    }
}

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class BlockOnly implements BlockSetting
{
    public function configure(\Pollora\Metabox\Block $block): void
    {
        $block->category('custom-category');
    }
}

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class GroupAndBlock implements BlockSetting, FieldGroupSetting
{
    public function configure(\Pollora\Metabox\FieldGroup|\Pollora\Metabox\Block $group): void
    {
        if (! $group instanceof \Pollora\Metabox\Block) {
            $group->closed(true);
        }
    }
}

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class GroupAndPage implements FieldGroupSetting, SettingsPageSetting
{
    public function configure(\Pollora\Metabox\FieldGroup|\Pollora\Metabox\SettingsPage $target): void
    {
        if ($target instanceof \Pollora\Metabox\SettingsPage) {
            $target->position(7);
        }
    }
}

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class PageOnly implements SettingsPageSetting
{
    public function configure(\Pollora\Metabox\SettingsPage $page): void
    {
        $page->position(9);
    }
}

#[Block('Custom block', 'custom-block')]
#[BlockOnly]
final class CustomBlock
{
    public function fields(): array
    {
        return [];
    }
}

#[Block('Grouped block', 'grouped-block')]
#[GroupAndBlock]
final class GroupedBlock
{
    public function fields(): array
    {
        return [];
    }
}

#[FieldGroup('Dual group', 'dual_group')]
#[GroupAndBlock]
final class DualGroup
{
    public function fields(): array
    {
        return [];
    }
}

#[SettingsPage('Custom page', 'custom-page')]
#[GroupAndPage]
final class DualPage {}

#[SettingsPage('Plain page', 'plain-page')]
#[PageOnly]
final class CustomPage {}

#[SettingsPage('Theme options again', 'theme-options')]
final class DuplicatePage {}

#[FieldGroup('Same ID as a page', 'theme-options')]
final class GroupSharingPageId
{
    public function fields(): array
    {
        return [];
    }
}

#[SettingsPage('Late page', 'late-page')]
final class LatePage {}

#[Block('No fields block', 'no-fields-block')]
final class BlockWithoutFields {}

#[Block('Page position', 'page-position')]
#[Position(3)]
final class BlockWithPageSetting
{
    public function fields(): array
    {
        return [];
    }
}

#[SettingsPage('Targeted page', 'targeted-page')]
#[PostTypes('page')]
final class PageWithTarget {}

#[FieldGroup('Allow dynamic', 'allow_dynamic')]
#[\AllowDynamicProperties]
final class WithForeignAttribute
{
    public function fields(): array
    {
        return [];
    }
}

#[FieldGroup('Multi targets', 'multi_targets')]
#[PostTypes('post')]
#[PostTypes('page', 'book')]
#[Taxonomies('genre')]
final class MultiTargets
{
    public function fields(): array
    {
        return [];
    }
}

#[FieldGroup('Camel')]
final class CamelCaseName
{
    public function fields(): array
    {
        return [];
    }
}

#[FieldGroup('Event group', 'event_group')]
#[PostTypes(Event::class)]
final class EventTarget
{
    public function fields(): array
    {
        return [];
    }
}

#[FieldGroup('Prebuilt', 'prebuilt')]
final class Prebuilt
{
    public function __construct(public string $origin = 'new') {}

    public function fields(): array
    {
        return [Text::make($this->origin, 'origin')];
    }
}

#[Block('Configured block', 'configured-block')]
final class ConfiguredBlock
{
    public function __construct(public string $origin = 'new') {}

    public function fields(): array
    {
        return [];
    }

    public function configuring(\Pollora\Metabox\Block $block): void
    {
        $block->category($this->origin);
    }
}
