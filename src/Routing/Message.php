<?php

namespace Nexius\Routing;


use App\Kernel;
use Nexius\Http\Middleware\Pipeline;

class Message
{
    protected $action;
    protected $uri;
    protected $middleware = [];

    public function __construct($uri, $action)
    {
        $this->uri = $uri;
        $this->action = $action;
    }

    public function middleware(array|string $middleware)
    {
        if (is_array($middleware))
            $this->middleware = array_merge($this->middleware, $middleware);
        elseif (is_string($middleware))
            $this->middleware[] = $middleware;
        return $this;
    }


    public function run($data)
    {
        $middleware = app()->call(Kernel::class)->middleware();
        $protected = array_intersect_key($middleware, array_flip($this->middleware));
        $pipeLine = new Pipeline($data, $this->action);
        foreach ($protected as $key => $item) {
            $pipeLine->add($item);
        }
        
        return $pipeLine->check();
    }
}