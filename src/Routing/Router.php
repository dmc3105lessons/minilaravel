<?php

namespace  MiniLaravel\Routing;

use MiniLaravel\Di\Container;
use MiniLaravel\Routing\Attributes\Route;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

class Router
{
    private array $endpoints = [];
    private TemplateRouteRegexCompilerFactory $matcherFactory;
   
    public function __construct(
        private Container $container
    )
    {
        $this->matcherFactory = $container->get(TemplateRouteRegexCompilerFactory::class);
    }

    public function dispatch() : mixed {
        $request = new Request();

        foreach ($this->endpoints as $endpoint) {
            $matcher = $endpoint[0];
            if ($matcher->match($request))
            {
                $controllerClass = $endpoint[1];
                $action = $endpoint[2];
                return $this->performControllerAction($controllerClass, $action, $request);
            }
        }
        throw new RuntimeException("No route for this request");

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

            $matcher = $this->matcherFactory->createMatcher($route->path, $route->method);
            $this->endpoints[] = [$matcher, $controllerClass, $method->getName()];
        }
    }

    public function performControllerAction(string $controllerClass, string $action, Request $request) : mixed {
        $reflection = new ReflectionClass($controllerClass);
        $method= $reflection->getMethod($action);

        $arguments = [];
        foreach ($method->getParameters() as $parameter)
        {
            $arguments[] = 
                $request->pathVariable($parameter->getName())
                ?: $this->container->get($parameter->getType()->getName());
        }
        $controller = $this->container->get($controllerClass);
        return $method->invokeArgs($controller, $arguments);
    }
}