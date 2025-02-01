<?php
namespace Framework\Helpers;

class Arr {
    private static array $data = [];

    public static function from(array $data): self {
        static::$data = $data;
        return new static;
    }

    public static function map(callable $callback): self {
        static::$data = array_map($callback, static::$data);
        return new static;
    }

    public static function filter(callable $callback): self {
        static::$data = array_filter(static::$data, $callback);
        return new static;
    }

    public static function reduce(callable $callback, mixed $initial = null): self {
        static::$data = array_reduce(static::$data, $callback, $initial);
        return new static;
    }

    public static function forEach(callable $callback): self {
        array_walk(static::$data, $callback);
        return new static;
    }

    public static function some(callable $callback): bool {
        foreach (static::$data as $item) {
            if ($callback($item)) return true;
        }
        return false;
    }

    public static function every(callable $callback): bool {
        foreach (static::$data as $item) {
            if (!$callback($item)) return false;
        }
        return true;
    }

    public static function find(callable $callback): mixed {
        foreach (static::$data as $item) {
            if ($callback($item)) return $item;
        }
        return null;
    }

    public static function findIndex(callable $callback): int {
        foreach (static::$data as $index => $item) {
            if ($callback($item)) return $index;
        }
        return -1;
    }

    public static function includes(mixed $searchElement): bool {
        return in_array($searchElement, static::$data);
    }

    public static function indexOf(mixed $searchElement): int|false {
        return array_search($searchElement, static::$data);
    }

    public static function join(string $separator = ','): string {
        return implode($separator, static::$data);
    }

    public static function reverse(): self {
        static::$data = array_reverse(static::$data);
        return new static;
    }

    public static function slice(int $start, ?int $end = null): self {
        static::$data = array_slice(static::$data, $start, $end);
        return new static;
    }

    public static function sort(?callable $callback = null): self {
        if ($callback) {
            usort(static::$data, $callback);
        } else {
            sort(static::$data);
        }
        return new static;
    }

    public static function unique(): self {
        static::$data = array_unique(static::$data);
        return new static;
    }

    public static function values(): self {
        static::$data = array_values(static::$data);
        return new static;
    }

    public static function keys(): self {
        static::$data = array_keys(static::$data);
        return new static;
    }

    public static function toArray(): array {
        return static::$data;
    }

    public static function toJson(int $options = 0): string {
        return json_encode(static::$data, $options);
    }
}