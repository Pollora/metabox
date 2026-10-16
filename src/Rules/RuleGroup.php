<?php

declare(strict_types=1);

namespace Pollora\Metabox\Rules;

use InvalidArgumentException;
use LogicException;

/**
 * Rules combined with AND (Rule::all()) or OR (Rule::any()).
 */
final class RuleGroup
{
    /**
     * @param  string  $relation  AND or OR.
     * @param  array<Rule>  $rules
     */
    public function __construct(
        private readonly string $relation,
        private readonly array $rules,
    ) {
        if ($rules === []) {
            throw new InvalidArgumentException('A rule group needs at least one rule.');
        }
    }

    /**
     * Build the settings Meta Box expects for a FieldGroup method.
     *
     * @param  string  $method  'include', 'exclude', 'show' or 'hide'.
     */
    public function toArray(string $method): array
    {
        $settings = ['relation' => $this->relation];

        foreach ($this->rules as $rule) {
            $value = $rule->valueFor($method);
            $key = $rule->key();

            if (! array_key_exists($key, $settings)) {
                $settings[$key] = $value;

                continue;
            }

            // Meta Box applies the relation to each input value, and a list of values matches any of them.
            if ($key === 'input_value') {
                $settings[$key] += $value;
            } elseif ($this->relation === 'OR' && is_array($value)) {
                $settings[$key] = array_merge($settings[$key], $value);
            } else {
                throw new LogicException("Meta Box cannot combine two '{$key}' rules with AND: use a single rule with all the values, which matches any of them.");
            }
        }

        return $settings;
    }
}
