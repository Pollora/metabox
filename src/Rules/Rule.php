<?php

declare(strict_types=1);

namespace Pollora\Metabox\Rules;

use InvalidArgumentException;

/**
 * A rule deciding whether a meta box is registered (MB Include Exclude) or displayed (MB Show Hide).
 *
 * Pass a rule, or a group of rules built with Rule::all() or Rule::any(), to
 * FieldGroup::include(), exclude(), show() or hide().
 */
final class Rule
{
    /**
     * Rules supported by include() and exclude().
     */
    private const INCLUDE_EXCLUDE = 'include/exclude';

    /**
     * Rules supported by show() and hide().
     */
    private const SHOW_HIDE = 'show/hide';

    /**
     * @param  string  $key  The Meta Box rule key.
     * @param  mixed  $value  The rule value.
     * @param  array<string>  $contexts  The methods supporting the rule.
     */
    private function __construct(
        private readonly string $key,
        private readonly mixed $value,
        private readonly array $contexts,
    ) {}

    /**
     * Match when all the rules match.
     */
    public static function all(Rule ...$rules): RuleGroup
    {
        return new RuleGroup('AND', $rules);
    }

    /**
     * Match when any of the rules matches.
     */
    public static function any(Rule ...$rules): RuleGroup
    {
        return new RuleGroup('OR', $rules);
    }

    /**
     * Match the posts with one of these IDs.
     */
    public static function postIds(int ...$ids): self
    {
        return new self('ID', $ids, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match the posts whose parent has one of these IDs.
     */
    public static function parentIds(int ...$ids): self
    {
        return new self('parent', $ids, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match the posts with one of these slugs.
     */
    public static function slugs(string ...$slugs): self
    {
        return new self('slug', $slugs, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match the posts using one of these page templates, e.g. 'templates/landing.php'.
     */
    public static function template(string ...$templates): self
    {
        return new self('template', $templates, [self::INCLUDE_EXCLUDE, self::SHOW_HIDE]);
    }

    /**
     * Match the posts with one of these post formats, e.g. 'video'.
     */
    public static function postFormat(string ...$formats): self
    {
        return new self('post_format', $formats, [self::SHOW_HIDE]);
    }

    /**
     * Match the posts in one of these categories, by ID, name or slug.
     *
     * Show and hide match IDs and names only, not slugs.
     */
    public static function category(int|string ...$categories): self
    {
        return new self('category', $categories, [self::INCLUDE_EXCLUDE, self::SHOW_HIDE]);
    }

    /**
     * Match the posts with one of these tags, by ID, name or slug.
     */
    public static function tag(int|string ...$tags): self
    {
        return new self('tag', $tags, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match the posts with one of these terms of a taxonomy, by ID, name or slug.
     *
     * Show and hide match IDs and names only, not slugs.
     */
    public static function terms(string $taxonomy, int|string ...$terms): self
    {
        return new self($taxonomy, $terms, [self::INCLUDE_EXCLUDE, self::SHOW_HIDE]);
    }

    /**
     * Match the posts in a child of one of these categories, by ID, name or slug.
     */
    public static function parentCategory(int|string ...$categories): self
    {
        return new self('parent_category', $categories, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match the posts with a child of one of these tags, by ID, name or slug.
     */
    public static function parentTag(int|string ...$tags): self
    {
        return new self('parent_tag', $tags, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match the posts with a child of one of these terms of a taxonomy, by ID, name or slug.
     */
    public static function parentTerms(string $taxonomy, int|string ...$terms): self
    {
        return new self('parent_'.$taxonomy, $terms, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match child posts, or posts without a parent with isChild(false).
     */
    public static function isChild(bool $isChild = true): self
    {
        return new self('is_child', $isChild, [self::INCLUDE_EXCLUDE, self::SHOW_HIDE]);
    }

    /**
     * Match when the current user has one of these roles.
     */
    public static function userRole(string ...$roles): self
    {
        return new self('user_role', $roles, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match when the current user has one of these IDs.
     */
    public static function userId(int ...$ids): self
    {
        return new self('user_id', $ids, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match when the current user has one of these capabilities, e.g. 'manage_options'.
     */
    public static function capability(string ...$capabilities): self
    {
        return new self('capability', $capabilities, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match when the edited user has one of these roles (MB User Meta).
     */
    public static function editedUserRole(string ...$roles): self
    {
        return new self('edited_user_role', $roles, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match when the edited user has one of these IDs (MB User Meta).
     */
    public static function editedUserId(int ...$ids): self
    {
        return new self('edited_user_id', $ids, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match when the callback returns true. It receives the meta box settings.
     *
     * @param  callable(array): bool  $callback
     */
    public static function custom(callable $callback): self
    {
        return new self('custom', $callback, [self::INCLUDE_EXCLUDE]);
    }

    /**
     * Match when an input of the edit screen has a value, e.g. inputValue('#featured', true) for a checked checkbox.
     *
     * @param  string  $selector  A CSS selector.
     */
    public static function inputValue(string $selector, mixed $value): self
    {
        return new self('input_value', [$selector => $value], [self::SHOW_HIDE]);
    }

    /**
     * Get the Meta Box rule key.
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Get the rule value, after checking that the FieldGroup method supports the rule.
     *
     * @param  string  $method  'include', 'exclude', 'show' or 'hide'.
     */
    public function valueFor(string $method): mixed
    {
        $context = in_array($method, ['show', 'hide'], true) ? self::SHOW_HIDE : self::INCLUDE_EXCLUDE;

        if (! in_array($context, $this->contexts, true)) {
            throw new InvalidArgumentException(sprintf(
                "The '%s' rule is not supported by %s(): it only works with %s().",
                $this->key,
                $method,
                $context === self::SHOW_HIDE ? 'include() and exclude' : 'show() and hide'
            ));
        }

        return $this->value;
    }
}
