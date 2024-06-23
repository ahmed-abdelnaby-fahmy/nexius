<?php

namespace Nexius\Http\Exception;

use Throwable;

class ValidationException extends \Exception
{
    public function __construct($message = 'Invalid Data', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}