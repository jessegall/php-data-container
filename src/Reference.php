<?php

namespace JesseGall\Data;


/**
 * This class is used to wrap a reference to a variable.
 */
class Reference
{

    /**
     * The reference value.
     *
     * @var mixed
     */
    private mixed $value;

    /**
     * Create a new reference instance.
     *
     * @param mixed $value
     */
    public function __construct(mixed &$value)
    {
        $this->value = &$value;
    }

    /**
     * Get the reference value.
     *
     * @return mixed
     */
    public function &get(): mixed
    {
        return $this->value;
    }

}