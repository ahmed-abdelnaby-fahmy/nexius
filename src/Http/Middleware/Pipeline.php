<?php

namespace Nexius\Http\Middleware;

use Nexius\Http\Request;
use Closure;
use Nexius\Http\Response;
use Nexius\Routing\Route;

class Pipeline
{
    protected $middleware = [];
    protected $action;
    protected $parameters = [];

    public function __construct(protected Route $route,protected Request $request)
    {
        $this->middleware = $route->middleware;
        $this->action = $route->action;
    }

    /**
     * Processes the middleware stack and then executes the action.
     *
     * @param array $parameters The parameters extracted from the route.
     * @return mixed The response from the action or the last middleware.
     */
    public function process(array $parameters = [])
    {
        $this->parameters = $parameters;
        $handler = $this->resolveAction($this->action);

        $pipeline = array_reduce($this->middleware, function ($next, $middlewareName) {
            return function ($request) use ($next, $middlewareName) {
                // Resolve middleware instance from the container
                $middleware = app()->get($middlewareName);

                // Call the middleware's handle method
                $resolve = $middleware->handle($request, $next);
                if ($resolve instanceof \Closure || $resolve instanceof Response)
                    return $resolve;
                else
                    throw new \Exception("Middleware $middlewareName denied the request.");

            };
        }, $handler);

        // Start the pipeline with the initial request
        return $pipeline($this->request);
    }

    /**
     * Resolves the final action to be executed at the end of the pipeline.
     *
     * @param mixed $action The action defined in the route.
     * @return Closure The closure wrapping the action execution.
     */
    protected function resolveAction($action): Closure
    {
        return function ($request) use ($action) {
            // Assuming $action is a callable in the format ['ControllerClass', 'method']
            // Adjust this logic as needed to support different formats
            $controller = app()->get($action[0]);
            $method = $action[1];

            // Merge request and route parameters
            return $controller->$method(...$this->parameters);
        };
    }
}
