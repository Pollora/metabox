<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

use InvalidArgumentException;
use LogicException;
use Pollora\Metabox\Fields\Field;

/**
 * Trait for showing or hiding an element depending on other fields (Meta Box Conditional Logic).
 *
 * Successive calls are combined with AND, the or*() methods combine them with OR.
 */
trait ConditionalLogic
{
    /**
     * The operators supported by Meta Box Conditional Logic.
     */
    private const CONDITION_OPERATORS = [
        '=', '!=', '>', '>=', '<', '<=',
        'in', 'contains', 'between', 'starts with', 'ends with', 'match',
        'not in', 'not contains', 'not between', 'not starts with', 'not ends with', 'not match',
    ];

    /**
     * The conditions that show the element.
     *
     * @var array<mixed>
     */
    protected array $visible;

    /**
     * The conditions that hide the element.
     *
     * @var array<mixed>
     */
    protected array $hidden;

    /**
     * Show the element when a field matches a value.
     *
     * Called with two arguments, the field must equal the value: visibleWhen('type', 'custom').
     * Called with three, the second one is the operator: visibleWhen('price', '>', 100).
     *
     * @param  Field|string  $field  The field, its ID, or another input name such as 'page_template'.
     */
    public function visibleWhen(Field|string $field, mixed $operator, mixed $value = null): static
    {
        return $this->addCondition('visible', 'and', $field, $operator, $value, func_num_args() === 3);
    }

    /**
     * Show the element when a field matches a value, or when the previous conditions match.
     */
    public function orVisibleWhen(Field|string $field, mixed $operator, mixed $value = null): static
    {
        return $this->addCondition('visible', 'or', $field, $operator, $value, func_num_args() === 3);
    }

    /**
     * Hide the element when a field matches a value. Same arguments as visibleWhen().
     */
    public function hiddenWhen(Field|string $field, mixed $operator, mixed $value = null): static
    {
        return $this->addCondition('hidden', 'and', $field, $operator, $value, func_num_args() === 3);
    }

    /**
     * Hide the element when a field matches a value, or when the previous conditions match.
     */
    public function orHiddenWhen(Field|string $field, mixed $operator, mixed $value = null): static
    {
        return $this->addCondition('hidden', 'or', $field, $operator, $value, func_num_args() === 3);
    }

    /**
     * Add a condition, stored in the format Meta Box expects:
     * a single [field, operator, value] condition, a list of conditions for AND,
     * or ['when' => [...], 'relation' => 'or'] for OR.
     */
    private function addCondition(string $setting, string $relation, Field|string $field, mixed $operator, mixed $value, bool $hasOperator): static
    {
        if (! $hasOperator) {
            [$operator, $value] = ['=', $operator];
        }

        if (! is_string($operator) || ! in_array(strtolower($operator), self::CONDITION_OPERATORS, true)) {
            throw new InvalidArgumentException(sprintf(
                "Invalid conditional logic operator '%s'. Allowed operators are '%s'.",
                is_scalar($operator) ? $operator : get_debug_type($operator),
                implode("', '", self::CONDITION_OPERATORS)
            ));
        }

        $condition = [$field instanceof Field ? $field->getId() : $field, strtolower($operator), $value];
        $current = (array) ($this->{$setting} ?? []);

        if ($current === []) {
            $this->{$setting} = $condition;

            return $this;
        }

        [$conditions, $currentRelation] = match (true) {
            isset($current['when']) => [(array) $current['when'], 'or'],
            is_array($current[0]) => [$current, 'and'],
            default => [[$current], null],
        };

        if ($currentRelation !== null && $currentRelation !== $relation) {
            throw new LogicException('Meta Box conditional logic cannot mix AND and OR conditions on the same element.');
        }

        $conditions[] = $condition;
        $this->{$setting} = $relation === 'or' ? ['when' => $conditions, 'relation' => 'or'] : $conditions;

        return $this;
    }
}
