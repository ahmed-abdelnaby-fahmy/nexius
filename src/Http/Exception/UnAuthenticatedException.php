<?php

namespace Nexius\Http\Exception;

use Throwable;

class UnAuthenticatedException extends \Exception
{
    public function __construct($message = 'UnAuthenticated', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}