<?php

namespace Nexius\Routing;

abstract class Controller
{
    protected $middleware = [];

    public function middleware($middleware, array $options = [])
    {
        foreach ((array)$middleware as $m) {
            $this->middleware[] = [
                'middleware' => $m,
                'options' => &$options,
            ];
        }
    }

}