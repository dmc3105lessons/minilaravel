<?php

namespace  MiniLaravel\Routing;

use MiniLaravel\Di\Container;
use MiniLaravel\Routing\Attributes\Route;
use ReflectionClass;
use ReflectionMethod;

class Router
{
    private array $mappings = [];
   
    public function __construct(
        private Container $container
    )
    {
        
    }

    public function dispatch() : mixed {
        $request = new Request();

        [$controllerClass, $action] = $this->mappings[$request->path()][$request->method()];

        return $this->performControllerAction($controllerClass, $action);
    }

    public function controller(string $controllerClass) : void {
        $reflection = new ReflectionClass($controllerClass);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Route::class);
            
            if ($attributes === [])
            {
                continue;
            }

            $route = $attributes[0]->newInstance();
            $this->mappings[$route->path][$route->method] = [$controllerClass, $method->getName()];
        }
    }

    public function performControllerAction(string $controllerClass, string $action) : mixed {
        $reflection = new ReflectionClass($controllerClass);
        $method= $reflection->getMethod($action);

        $arguments = [];
        foreach ($method->getParameters() as $parameter)
        {
            $arguments[] = $this->container->get($parameter->getType()->getName());
        }
        $controller = $this->container->get($controllerClass);
        return $method->invokeArgs($controller, $arguments);
    }
}