<?php

namespace Nexius\Config;

use App\Kernel;
use Nexius\Database\Config as DatabaseConfig;
use Nexius\Http\Request;
use Nexius\Http\Response;
use Nexius\Routing\RouteManager;
use Nexius\Http\Middleware\Middleware;
use Nexius\Routing\Router;

// Ensure this is used or defined as needed.

class Application
{
    protected $router;
    protected $env;

    public function __construct($dir)
    {
        $this->bootstrapEnvironment($dir);
        $this->registerServices();
        $this->configureRoutes();
    }

    protected function bootstrapEnvironment($dir)
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        app()->register('env', new Dotenv($dir . '/.env'));
        ini_set('display_startup_errors', env('APP_DEBUG', '1'));
    }

    protected function registerServices()
    {

        // Assuming Kernel and Config are correctly namespaced
        app()->singleton(Kernel::class);
        app()->singleton(DatabaseConfig::class);

        // Router and Request are now initialized here, leveraging the container if needed
        app()->singleton(RouteManager::class, function () {
            return new RouteManager();
        });
        app()->singleton(Request::class, function () {
            return (new Request());
        });

        // Assuming Dotenv is registered and accessible as 'env'
        // Note: This might require adjustments based on your Dotenv setup
        $this->router = app()->get(Router::class);
    }

    protected function configureRoutes()
    {
        $kernel = app()->get(Kernel::class);
        // Ensure Kernel::routes method exists and is expecting a RouteManager instance
        $kernel->routes();
    }

    public function start()
    {
        // Assuming the start method is responsible for handling the incoming HTTP request
        $request = app()->get(Request::class);
        // Dispatch the request using the router
        $response = $this->router->dispatch($request);
        // Send the response back to the client
        if ($response instanceof Response) {
            $response->send();
        } else
            \response($response)->send();
    }

    public function messager($data)
    {
        // Ensure Router::callMessage or equivalent logic exists for handling messages
        return $this->router->callMessage($data->event, $data->data);
    }
}
