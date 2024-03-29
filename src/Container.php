<?php

namespace JesseGall\Data;


use Closure;

/**
 * This interface is used to define a data container.
 */
interface Container
{

    /**
     * Set a key value pair
     *
     * @param string $key
     * @param mixed|null $value
     * @return $this
     */
    public function set(string $key, mixed $value = null): static;

    /**
     * Get the value of the given key
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function &get(string $key = null, mixed $default = null): mixed;

    /**
     * Check if the key exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove the given key
     *
     * @param string $key
     * @return $this
     */
    public function forget(string $key): static;

    /**
     * Flatten the data
     *
     * @param string|null $key
     * @return array
     */
    public function flatten(string $key = null): array;

    /**
     * Merge the given data with the existing data
     *
     * @param string $key
     * @param array|null $data
     * @return $this
     */
    public function merge(string $key, array $data = null): static;

    /**
     * Merge the given data with the existing data, but only if the key does not exist
     *
     * @param string $key
     * @param array|null $data
     * @return $this
     */
    public function mergeDistinct(string $key, array $data = null): static;

    /**
     * Map the data to the result of the given callback
     *
     * @param string|Closure $key
     * @param callable $callback
     * @return mixed
     */
    public function map($key, $callback = null): mixed;

    /**
     * Clear the data container
     *
     * @return $this
     */
    public function clear(): static;


}