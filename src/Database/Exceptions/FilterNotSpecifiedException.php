<?php

namespace Nexius\Database\Exceptions;

class FilterNotSpecifiedException extends \Exception
{
    // You can customize the exception further if needed,
    // for example, by setting a default message or code.
    public function __construct($message = "Filter criteria must be specified for this operation.", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
