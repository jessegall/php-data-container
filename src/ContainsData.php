<?php

namespace JesseGall\Data;

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
     * @var array<TKey, TValue>
     */
    protected array $data = [];

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
     * @param array|Reference $data
     * @return $this
     */
    public function setData(array|Reference $data): static
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
            if (! isset($data[$segment]) || ! is_array($data[$segment])) {
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
            if (! is_array($data) || ! isset($data[$segment])) {
                return $default;
            }

            $data = &$data[$segment];
        }

        return $data;
    }

    /**
     * Delete the given key
     *
     * @param string $key
     * @return $this
     */
    public function delete(string $key): static
    {
        $data = &$this->data;

        $segments = explode($this->delimiter, $key);

        $key = array_pop($segments);

        foreach ($segments as $segment) {
            if (! isset($data[$segment])) {
                return $this;
            }

            $data = &$data[$segment];

            if (! is_array($data)) {
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
            if (! is_array($data) || ! isset($data[$segment])) {
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

            if (is_array($value)) {
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
     * @param string|null $key
     * @param array $data
     * @return $this
     */
    public function merge(string|null $key, array $data): static
    {
        foreach ($data as $itemKey => $value) {
            $itemKey = is_null($key) ? $itemKey : $key . $this->delimiter . $itemKey;

            if (is_array($value)) {
                $this->merge($itemKey, $value);
            } else {
                $this->set($itemKey, $value);
            }
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

}
