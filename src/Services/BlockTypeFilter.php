<?php

declare(strict_types=1);

namespace Pollora\Metabox\Services;

/**
 * Service for filtering blocks by post type
 */
class BlockTypeFilter
{
    /**
     * Stored restrictions for blocks
     *
     * @var array<string, array<mixed>>
     */
    protected static array $restrictions = [];

    /**
     * Flag to track if filter has been added
     */
    protected static bool $filterAdded = false;

    /**
     * Register a block's post type restrictions
     *
     * @param  string  $blockId  The block identifier
     * @param  array<mixed>  $restrictions  The restrictions configuration
     */
    public static function registerRestrictions(string $blockId, array $restrictions): void
    {
        self::$restrictions[$blockId] = array_merge(self::$restrictions[$blockId] ?? [], $restrictions);
        self::setupFilter();
    }

    /**
     * Filter blocks based on post type restrictions
     *
     * @param  mixed  $allowed_blocks  List of allowed blocks
     * @param  object  $context  Editor context
     * @return mixed Filtered list of allowed blocks
     */
    public static function filterBlockTypes($allowed_blocks, $context): mixed
    {
        if (! isset($context->post) || self::$restrictions === []) {
            return $allowed_blocks;
        }

        $removed = self::blocksRemovedFor($context->post->post_type);

        // Keep the value untouched when nothing is removed: expanding `true` to the server-side
        // registry would drop the blocks registered in JavaScript only.
        if ($removed === [] || $allowed_blocks === false) {
            return $allowed_blocks;
        }

        if ($allowed_blocks === true) {
            $allowed_blocks = array_keys(\WP_Block_Type_Registry::get_instance()->get_all_registered());
        }

        if (! is_array($allowed_blocks)) {
            return $allowed_blocks;
        }

        return array_values(array_filter($allowed_blocks, fn ($blockName) => ! isset($removed[$blockName])));
    }

    /**
     * Get the registered block name for a Meta Box block ID, as MB Blocks does.
     */
    public static function blockName(string $blockId): string
    {
        return 'meta-box/'.sanitize_title($blockId);
    }

    /**
     * Get the registered restrictions, keyed by block ID.
     *
     * @return array<string, array<mixed>>
     */
    public static function getRestrictions(): array
    {
        return self::$restrictions;
    }

    /**
     * Get the names of the restricted blocks not allowed on a post type.
     *
     * @return array<string, true> Block names as keys.
     */
    protected static function blocksRemovedFor(string $postType): array
    {
        $removed = [];

        foreach (self::$restrictions as $blockId => $restrictions) {
            $notAllowed = isset($restrictions['allowed']) && ! in_array($postType, $restrictions['allowed'], true);
            $excluded = isset($restrictions['excluded']) && in_array($postType, $restrictions['excluded'], true);

            if ($notAllowed || $excluded) {
                $removed[self::blockName($blockId)] = true;
            }
        }

        return $removed;
    }

    /**
     * Setup the WordPress filter for block types
     */
    protected static function setupFilter(): void
    {
        if (! self::$filterAdded) {
            add_filter('allowed_block_types_all', [self::class, 'filterBlockTypes'], 99999, 2);
            self::$filterAdded = true;
        }
    }
}
