<?php

namespace JesseGall\Data;

use ArrayAccess;

/**
 * This trait is used to add data container functionality to a class.
 * It provides dot notation for setting and getting data.
 *
 * @template TKey of array-key
 * @template TValue mixed
 */
trait ContainsData
{

    /**
     * The data container.
     *
     * @var array<TKey, TValue>|ArrayAccess
     */
    protected array|ArrayAccess $data = [];

    /**
     * The delimiter used for accessing nested data.
     *
     * @var string
     */
    protected string $delimiter = '.';

    /**
     * Set the data container.
     * If the given data is wrapped in a reference object,
     * the data container will be a reference to the value of the reference object.
     *
     * @param array|Reference|ArrayAccess $data
     * @return $this
     */
    public function setData(array|Reference|ArrayAccess $data): static
    {
        if ($data instanceof Reference) {
            $this->data = &$data->get();
        } else {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * Set a key value pair
     * If the given value is wrapped in a reference object,
     * the value will be a reference to the value of the reference object.
     *
     * @param string $key
     * @param mixed|null $value
     * @return $this
     */
    public function set(string $key, mixed $value = null): static
    {
        $data = &$this->data;

        $segments = explode($this->delimiter, $key);

        $key = array_pop($segments);

        foreach ($segments as $segment) {
            if (!isset($data[$segment]) || !$this->isArrayAccessible($data[$segment])) {
                $data[$segment] = [];
            }

            $data = &$data[$segment];
        }

        if ($value instanceof Reference) {
            $data[$key] = &$value->get();
        } else {
            $data[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the value of the given key
     * If the given key is null, the entire data container will be returned.
     * If the given key is not found, the given default value will be returned.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function &get(string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->data;
        }

        $data = &$this->data;

        foreach (explode($this->delimiter, $key) as $segment) {
            if (!$this->isArrayAccessible($data) || !isset($data[$segment])) {
                return $default;
            }

            $data = &$data[$segment];
        }

        return $data;
    }

    /**
     * Remove the given key
     *
     * @param string $key
     * @return $this
     */
    public function forget(string $key): static
    {
        $data = &$this->data;

        $segments = explode($this->delimiter, $key);

        $key = array_pop($segments);

        foreach ($segments as $segment) {
            if (!isset($data[$segment])) {
                return $this;
            }

            $data = &$data[$segment];

            if (!$this->isArrayAccessible($data)) {
                return $this;
            }
        }

        unset($data[$key]);

        return $this;
    }

    /**
     * Check if the given key exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $data = $this->data;

        foreach (explode($this->delimiter, $key) as $segment) {
            if (!$this->isArrayAccessible($data) || !isset($data[$segment])) {
                return false;
            }

            $data = $data[$segment];
        }

        return true;
    }

    /**
     * Get all data as a flat array
     *
     * @param string|null $prefix
     * @return array
     */
    public function flatten(string $prefix = null): array
    {
        $result = [];

        $data = $this->get($prefix);

        foreach ($data as $itemKey => $value) {
            $nextKey = is_null($prefix) ? $itemKey : $prefix . $this->delimiter . $itemKey;

            if ($this->isArrayAccessible($value)) {
                $result[] = $this->flatten($nextKey);
            } else {
                $result[] = [$nextKey => $value];
            }
        }

        return array_merge(...$result);
    }

    /**
     * Merge the given data into the data container
     *
     * @param string|array|null $key
     * @param array|null $data
     * @return $this
     */
    public function merge(string|null|array $key, array $data = null): static
    {
        if ($this->isArrayAccessible($key)) {
            $data = $key;
            $key = null;
        }

        foreach ($data as $itemKey => $value) {
            $itemKey = is_null($key) ? $itemKey : $key . $this->delimiter . $itemKey;

            if ($this->isArrayAccessible($value)) {
                $this->merge($itemKey, $value);
            } else {
                $this->set($itemKey, $value);
            }
        }

        return $this;
    }

    /**
     * @param string|array|null $key
     * @param array|null $data
     * @return $this
     */
    public function mergeDistinct(string|null|array $key, array $data = null): static
    {
        if ($this->isArrayAccessible($key)) {
            $data = $key;
            $key = null;
        }

        foreach ($data as $itemKey => $value) {
            $itemKey = is_null($key) ? $itemKey : $key . $this->delimiter . $itemKey;

            if ($this->isArrayAccessible($value)) {
                $this->mergeDistinct($itemKey, $value);
            } else {
                if (!$this->has($itemKey)) {
                    $this->set($itemKey, $value);
                }
            }
        }

        return $this;
    }


    /**
     * Map the data to the result of the given callback
     *
     * @param string|Closure $key
     * @param callable $callback
     * @return $this
     */
    public function map($key, $callback = null): mixed
    {
        if (is_callable($key)) {
            $callback = $key;
            $key = null;
        }

        $data = $this->get($key);

        if ($this->isArrayAccessible($data)) {
            $data = array_map($callback, $data);
        } else {
            $data = $callback($data);
        }

        return $data;
    }

    /**
     * Clear the data container
     *
     * @return ContainsData
     */
    public function clear(): static
    {
        if ($this->data instanceof ArrayAccess) {
            foreach ($this->data as $key => $value) {
                unset($this->data[$key]);
            }
        } else {
            $this->data = [];
        }

        return $this;
    }

    /**
     * Set the delimiter used for accessing nested data
     *
     * @param string $delimiter
     * @return $this
     */
    public function setDelimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }


    /**
     * Check if the given data is an array or an instance of ArrayAccess.
     *
     * @param mixed $data
     * @return bool
     */
    private function isArrayAccessible(mixed $data): bool
    {
        return is_array($data) || $data instanceof ArrayAccess;
    }

}
