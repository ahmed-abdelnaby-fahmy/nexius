<?php

namespace Nexius\Config;

class Container
{
    private static $instance = null;
    private $instances = [];
    private $bindings = [];

    // Private constructor to prevent direct instantiation.
    private function __construct() {}

    // Prevent cloning.
    private function __clone() {}

    // Prevent unserialization.
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
    /**
     * Returns the single instance of the container.
     *
     * @return Container The single instance of the container.
     */
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function singleton($abstract, $concrete = null)
    {
        $this->bindings[$abstract] = function ($container) use ($abstract,$concrete) {
            if (is_null($concrete)) {
                $concrete = $abstract;
            }
            if (is_callable($concrete)) {
                return $concrete($container);
            }
            return $container->build($concrete);
        };
    }

    public function build($concrete)
    {
        $reflector = new \ReflectionClass($concrete);
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);
        return $reflector->newInstanceArgs($dependencies);
    }

    protected function resolveDependencies($parameters)
    {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType() && !$parameter->getType()->isBuiltin()
                ? $parameter->getType()->getName()
                : null;
            if ($dependency === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Can not resolve class dependency {$parameter->name}.");
                }
            } else {
                $dependencies[] = $this->get($dependency);
            }
        }
        return $dependencies;
    }

    public function get($abstract)
    {
        if (!isset($this->instances[$abstract])) {
            if (isset($this->bindings[$abstract])) {
                $this->instances[$abstract] = call_user_func($this->bindings[$abstract], $this);
            } else {
                $this->instances[$abstract] = $this->build($abstract);
            }
        }
        return $this->instances[$abstract];
    }

    public function register($name, $class)
    {
        $this->instances[$name] = is_callable($class) ? $class($this) : $class;
    }

    public function has($abstract)
    {
        return isset($this->instances[$abstract]) || isset($this->bindings[$abstract]);
    }
}
