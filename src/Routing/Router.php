<?php

namespace Nexius\Routing;


use Nexius\Http\Exception\ValidationException;

class Router
{
    /**
     * @var RouteManager
     */
    protected $routes;

    static private $instance = null;


    public static function __callStatic($method, $arguments)
    {
        if (!self::$instance)
            self::$instance = new RouteManager();

        try {
            return self::$instance->{$method}(...$arguments);
        }catch (ValidationException $validationException) {
            return response()->json($validationException->getMessage());
        } catch (\Throwable $e) {
            \response([$e->getMessage(), $e->getTraceAsString()], $e->getCode())->send();
        }
    }


    public function __call($method, $arguments)
    {
        if (!self::$instance) {
            self::$instance = new RouteManager();
        }
        try {
            return self::$instance->{$method}(...$arguments);
        } catch (ValidationException $validationException) {
            return \response()->json($validationException->getMessage());
        } catch (\Throwable $e) {
            \response([$e->getMessage(), $e->getTraceAsString()], $e->getCode())->send();
        }
    }

}