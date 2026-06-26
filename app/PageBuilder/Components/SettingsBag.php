<?php

namespace App\PageBuilder\Components;

use ArrayAccess;
use JsonSerializable;

class SettingsBag implements ArrayAccess, JsonSerializable
{
    public function __construct(private array $values = []) {}

    public function all(): array
    {
        return $this->values;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->values[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->values[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset !== null) {
            $this->values[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->values[$offset]);
    }

    public function jsonSerialize(): array
    {
        return $this->values;
    }
}
