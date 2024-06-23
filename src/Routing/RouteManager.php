<?php

namespace Nexius\Routing;

use Nexius\Http\Request;
use Nexius\Config\Container;
use Nexius\Http\Exception\NotFoundHttpException;

class RouteManager
{
    protected $routes = [];
    protected $groupOptions = [];

    public function __construct()
    {
        // The container can be accessed anywhere using Container::getInstance(),
        // so it's not necessarily to be passed to or stored in RouteManager.
    }

    public function addRoute($methods, $uri, $action)
    {
        $methods = (array)$methods;
        // Apply group prefix if present.
        $uri = $this->applyGroupPrefix($uri);
        // Create a new Route instance.
        $route = new Route($methods, $uri, $action);
        // Apply any middleware or other group options to the route.
        $this->applyGroupOptionsToRoute($route);
        // Add the route to the collection of routes.
        $this->routes[] = $route;
        return $route;
    }

    protected function applyGroupPrefix($uri): string
    {
        // Checks if there's a prefix set in the current group options and applies it to the URI.
        return isset($this->groupOptions['prefix']) ? rtrim($this->groupOptions['prefix'], '/') . '/' . ltrim($uri, '/') : $uri;
    }

    protected function applyGroupOptionsToRoute(Route $route)
    {
        // If there are middleware options set for the group, apply them to the route.
        if (isset($this->groupOptions['middleware'])) {
            $route->middleware($this->groupOptions['middleware']);
        }
        // Extend this method to apply other group options as needed.
    }
    // Method shortcuts for common HTTP verbs
    public function get($uri, $action) { return $this->addRoute('GET', $uri, $action); }
    public function post($uri, $action) { return $this->addRoute('POST', $uri, $action); }
    public function put($uri, $action) { return $this->addRoute('PUT', $uri, $action); }
    public function delete($uri, $action) { return $this->addRoute('DELETE', $uri, $action); }
    public function patch($uri, $action) { return $this->addRoute('PATCH', $uri, $action); }
    public function options($uri, $action) { return $this->addRoute('OPTIONS', $uri, $action); }
    public function any($uri, $action) { return $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], $uri, $action); }

    public function group(array $options, \Closure $callback)
    {
        $previousGroupOptions = $this->groupOptions;
        $this->groupOptions = $options;

        // Call the group callback, which should add routes that inherit the group options
        $callback($this);

        // Restore previous group options in case of nested groups
        $this->groupOptions = $previousGroupOptions;
    }

    public function dispatch(Request $request)
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request)) {
                return $route->run($request);
            }
        }
        throw new NotFoundHttpException('No route matched.');
    }
}
