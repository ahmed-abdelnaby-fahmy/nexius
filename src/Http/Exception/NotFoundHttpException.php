<?php

namespace Nexius\Http\Exception;

use Throwable;

class NotFoundHttpException extends HttpException
{
    /**
     * @param string|null $message The internal exception message
     * @param Throwable|null $previous The previous exception
     * @param int $code The internal exception code
     * @param array $headers
     */
    public function __construct(?string $message = '', Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(404, $message, $previous, $headers, $code);
    }
}
