<?php

use JesseGall\Data\Container;
use JesseGall\Data\ContainsData;
use JesseGall\Data\Reference;

if (! function_exists('container')) {
    /**
     * Create a new data container.
     *
     * @param array|Reference $data
     * @return Container
     */
    function container(array|Reference|ArrayAccess $data = []): Container
    {
        $container = new class implements Container {
            use ContainsData;
        };

        $container->setData($data);

        return $container;
    }
}
