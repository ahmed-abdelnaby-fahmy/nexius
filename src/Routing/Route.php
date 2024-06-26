<?php

namespace Nexius\Routing;

use App\Kernel;
use Nexius\Http\Request;
use Nexius\Config\Container;
use Nexius\Http\Exception\MethodNotAllowedException;
use Nexius\Http\Middleware\Pipeline;

class Route
{
    protected $uri;
    protected $methods;
    protected $name;
    public $action;
    public $middleware = [];
    protected $parameters = [];

    public function __construct(array $methods, string $uri, $action)
    {
        $this->uri = $uri;
        $this->methods = $methods;
        $this->action = $action;
    }

    public function middleware($middleware): self
    {
        $kernelMiddleware = app()->get(Kernel::class)->middleware();
        $protected = array_intersect_key($kernelMiddleware, array_flip($middleware));
        $this->middleware = array_merge($this->middleware, array_values($protected));
        return $this;
    }

    public function name($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function run(Request $request)
    {
        if (!$this->matches($request)) {
            throw new MethodNotAllowedException('Method not allowed');
        }

        // Assuming middleware handling is done in the Pipeline
        $pipeline = new Pipeline($this, $request);

        return $pipeline->process($this->parameters);
    }

    public function matches(Request $request): bool
    {
        // Check if request method is allowed for this route
        if (!in_array($request->getMethod(), $this->methods, true)) {
            return false;
        }
        $routeUri = '/' . ltrim($this->uri, '/');
        $pathInfo = '/' . ltrim($request->getPathInfo(), '/');

        $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $routeUri);
        $routePattern = str_replace('/', '\/', $routePattern);
        $reflection = new \ReflectionMethod($this->action[0],$this->action[1]);
        $params = $reflection->getParameters();

        if (preg_match('/^' . $routePattern . '$/', $pathInfo, $matches)) {
            array_shift($matches); // Remove full match
            $parameterNames = [];
            if (preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $routeUri, $parameterNames)) {
                $this->parameters = array_combine($parameterNames[1], $matches);
            }
            foreach ($params as $param) {
                $type = $param->getType();
                if ($type && $type->getName() === Request::class) {
                    $this->parameters[$param->getName()] = \request();
                }elseif (!array_key_exists($param->getName(), $this->parameters))
                    throw new \Exception("Route parameters do not match expected parameters.");
            }
            return true;
        }

        return false;
    }
}
