<?php

namespace JesseGall\HasArrayData;

trait HasArrayData
{

    /**
     * The data
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Get an item using dot notation.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->data;
        }

        if (! $this->has($key)) {
            return $default;
        }

        $data = $this->data;

        $segments = explode('.', $key);

        foreach ($segments as $segment) {
            $data = $data[$segment];
        }

        return $data;
    }

    /**
     * Set item using dot notation.
     *
     * @param string|array $key
     * @param mixed|null $value
     * @return array
     */
    public function set(string|array $key, mixed $value = null): array
    {
        $data = &$this->data;

        if (is_array($key)) {
            return $data = $key;
        }

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

        return $this->data;
    }

    /**
     * Check if an item exists using dot notation
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $data = $this->data;

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

}