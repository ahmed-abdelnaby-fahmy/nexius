<?php

namespace Nexius\Http\Exception;

interface HttpExceptionInterface
{
    /**
     * Returns the status code.
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Returns response headers.
     *
     * @return array
     */
    public function getHeaders();
}