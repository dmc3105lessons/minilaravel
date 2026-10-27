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

        // [$controllerClass, $action] = $this->mappings[$request->path()][$request->method()];
        foreach ($this->endpoints as $endpoint) {
            $matcher = $endpoint[0];
            if ($matcher->match($request))
            {
                $controllerClass = $endpoint[1];
                $action = $endpoint[2];
                return $this->performControllerAction($controllerClass, $action);
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
            //$this->mappings[$route->path][$route->method] = [$controllerClass, $method->getName()];
        }
    }

    public function performControllerAction(string $controllerClass, string $action) : mixed {
        $reflection = new ReflectionClass($controllerClass);
        $method= $reflection->getMethod($action);

        $arguments = [];
        $request = new Request();
        $matcher = null;
        foreach ($this->endpoints as $endpoint) {
            if ($endpoint[1] == $controllerClass and $endpoint[2] == $action) {
                $matcher = $endpoint[0];
                break;
            }
        }
        $template = $matcher->templatePathRegex;
        $matches = [];
        preg_match($template, $request->path(), $matches);
        foreach ($method->getParameters() as $parameter)
        {
            if (isset($matches[$parameter->getName()])) {
                $arguments[] = $matches[$parameter->getName()];
                continue;
            }
            $arguments[] = $this->container->get($parameter->getType()->getName());
        }
        $controller = $this->container->get($controllerClass);
        return $method->invokeArgs($controller, $arguments);
    }
}