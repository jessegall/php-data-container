<?php

namespace JesseGall\ContainsData;

use Exception;

class GetAsReferenceMissingException extends Exception
{

    public function __construct(string $key)
    {
        parent::__construct("Trying to get a value as reference, but container does not contain $key");
    }

}