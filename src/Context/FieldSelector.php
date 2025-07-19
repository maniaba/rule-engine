<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Context;

use App\Entities\BaseEntity;
use BackedEnum;

/**
 * Field selector for accessing nested properties and filtering arrays.
 *
 * @see FieldSelectorTest
 */
final class FieldSelector
{
    private array $cache = []; // Cache za selektore

    public function __construct(
        private readonly array|object $data,
    ) {}

    public function getField(string $selector): mixed
    {
        // Provjera u kešu
        if (\array_key_exists($selector, $this->cache)) {
            return $this->cache[$selector];
        }

        $parts = explode('->', $selector);
        $current = $this->data;

        foreach ($parts as $part) {
            $current = $this->resolvePart($current, $part);
        }

        // Spremanje rezultata u keš
        $this->cache[$selector] = $current;

        return $current;
    }

    public function clearCache(): void
    {
        $this->cache = []; // Čišćenje keša
    }

    private function resolvePart(mixed $data, string $part): mixed
    {
        // Match array access pattern (e.g., key[index], key[id:>=2])
        if (preg_match('/^(\w+)(\[(.*?)\])?$/', $part, $matches)) {
            $key = $matches[1];
            $filter = $matches[3] ?? null;

            // Access object or array
            if (\is_array($data) && \array_key_exists($key, $data)) {
                $data = $data[$key];
            } elseif (\is_object($data) && property_exists($data, $key)) {
                $data = $data->{$key};
            } elseif (\is_object($data) && method_exists($data, 'get'.ucfirst($key))) {
                $data = $data->{'get'.ucfirst($key)}();
            } elseif ($data instanceof BaseEntity) {
                $data = $data->{$key};
            } else {
                throw new \InvalidArgumentException("Key '{$key}' does not exist.");
            }

            // Handle filter if present
            if (null !== $filter) {
                return $this->resolveArray($data, $filter);
            }

            return $data;
        }

        throw new \InvalidArgumentException("Invalid selector part: '{$part}'.");
    }

    private function resolveArray(array $array, string $filter): mixed
    {
        // Numeric index access
        if (is_numeric($filter)) {
            return $array[(int) $filter] ?? throw new \InvalidArgumentException("Index '{$filter}' does not exist in array.");
        }

        // Key-value filtering with custom operators
        if (preg_match('/^(\w+):(>=|<=|>|<(?!>)|=|!=)?(.+)$/', $filter, $matches)) {
            $key = $matches[1];
            $operator = '' !== $matches[2] ? $matches[2] : '=';

            // Provjera je li operator podržan
            $supportedOperators = ['=', '>=', '<=', '>', '<', '!='];

            if (! \in_array($operator, $supportedOperators, true)) {
                throw new \InvalidArgumentException("Unsupported operator '{$operator}' in filter '{$filter}'");
            }

            $value = self::castValue($matches[3]);

            foreach ($array as $item) {
                $itemValue = \is_array($item) ? $item[$key] ?? null : (property_exists($item, $key) ? $item->{$key} : ($item instanceof BaseEntity ? $item->{$key} : null));

                if (null === $itemValue) {
                    continue;
                }

                if (self::evaluateCondition($itemValue, $operator, $value)) {
                    return $item;
                }
            }

            throw new \InvalidArgumentException("No matching element found for '{$filter}' in array.");
        }

        throw new \InvalidArgumentException("Invalid array filter '{$filter}'.");
    }

    private static function castValue(string $value): mixed
    {
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        if (strtolower($value) === 'true') {
            return true;
        }

        if (strtolower($value) === 'false') {
            return false;
        }

        return $value;
    }

    private static function evaluateCondition(mixed $itemValue, string $operator, mixed $value): bool
    {
        // Ako je $itemValue instanca BackedEnum, koristimo njegovu podloženu vrijednost
        if ($itemValue instanceof \BackedEnum) {
            $itemValue = $itemValue->value;
        } elseif ($itemValue instanceof \UnitEnum) {
            $itemValue = $itemValue->name;
        }

        return match ($operator) {
            '=' => $itemValue === $value,
            '>' => $itemValue > $value,
            '>=' => $itemValue >= $value,
            '<' => $itemValue < $value,
            '<=' => $itemValue <= $value,
            '!=' => $itemValue !== $value,
            default => throw new \InvalidArgumentException("Unsupported operator '{$operator}'"),
        };
    }
}
