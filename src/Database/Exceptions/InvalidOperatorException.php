<?php

namespace Nexius\Database\Exceptions;

class InvalidOperatorException extends \Exception
{
    // You can customize the exception further if needed,
    // for example, by setting a default message or code.
    public function __construct($message = "The specified operator is invalid or not supported.", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
