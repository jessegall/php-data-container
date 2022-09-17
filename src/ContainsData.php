<?php

namespace JesseGall\ContainsData;

use Closure;

trait ContainsData
{

    /**
     * The container which holds the data.
     *
     * @var array
     */
    protected array $__container = [];

    /**
     * Returns a reference to the data container.
     *
     * To point to a different data container the method can be overridden.
     * It is also possible to pass a new reference as argument.
     *
     * @param array|null $container
     * @return array
     */
    public function &container(array &$container = null): array
    {
        if (! is_null($container)) {
            $this->__container = &$container;
        }

        return $this->__container;
    }

    /**
     * Get an item using dot notation.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (! $this->has($key)) {
            return $default;
        }

        $data = $this->container();

        $segments = explode('.', $key);

        foreach ($segments as $segment) {
            $data = $data[$segment];
        }

        return $data;
    }

    /**
     * Get an item as reference using dot notation.
     *
     * @param string $key
     * @return mixed
     * @throws GetAsReferenceMissingException
     */
    public function &getAsReference(string $key): mixed
    {
        if (! $this->has($key)) {
            throw new GetAsReferenceMissingException($key);
        }

        $data = &$this->container();

        if (str_contains($key, '.')) {
            [$segment] = explode('.', $key);

            $container = new class { use ContainsData; };

            $container->container($data[$segment]);

            return $container->getAsReference(str_replace("$segment.", '', $key));
        }

        return $data[$key];
    }

    /**
     * Set item using dot notation.
     *
     * @param string $key
     * @param mixed|null $value
     * @return array
     */
    public function set(string $key, mixed $value = null): array
    {
        $data = &$this->container();

        $segments = explode('.', $key);

        foreach ($segments as $index => $segment) {
            if (count($segments) === 1) {
                break;
            }

            unset($segments[$index]);

            if (! isset($data[$segment]) || ! is_array($data[$segment])) {
                $data[$segment] = [];
            }

            $data = &$data[$segment];
        }

        $data[array_shift($segments)] = $value;

        return $this->container();
    }

    /**
     * Check if an item exists using dot notation.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $data = $this->container();

        $segments = explode('.', $key);

        foreach ($segments as $segment) {
            if (! is_array($data)) {
                return false;
            }

            if (! array_key_exists($segment, $data)) {
                return false;
            }

            $data = $data[$segment];
        }

        return true;
    }

    /**
     * Map the item to the result of the callback.
     * If the key points to an array, map each item of the array.
     *
     * When $replace is true, replace the item with the result
     *
     * @param string $key
     * @param Closure $callback
     * @param bool $replace
     * @return mixed
     */
    public function map(string $key, Closure $callback, bool $replace = false): mixed
    {
        $item = $this->get($key);

        if (! is_array($item)) {
            $item = $callback($item);
        } else {
            foreach ($item as $_key => $_item) {
                $item[$_key] = $callback($_item, $_key);
            }
        }

        if ($replace) {
            $this->set($key, $item);
        }

        return $item;
    }

    /**
     * Merge an array with the container
     *
     * @param array $data
     * @param string $prefix
     * @return array
     */
    public function merge(array $data, string $prefix = ''): array
    {
        foreach ($data as $_key => $value) {
            $key = $prefix . $_key;

            if (is_array($value)) {
                $this->merge($value, "$key.");
            } else {
                $this->set($key, $value);
            }
        }

        return $this->container();
    }

}