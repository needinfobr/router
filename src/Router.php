<?php

namespace Needinfo\Router;

use Closure;
use Exception;

/**
 * Class Router
 * @package Needinfo\Router
 */
class Router
{
    private string $projectUrl;
    private array $routes;
    private ?string $group = null;
    private ?string $namespace = null;
    private ?string $error = null;
    
    /** @var mixed dependency container */
    private mixed $container = null;

    /**
     * Router constructor.
     * @param string $projectUrl
     */
    public function __construct(string $projectUrl)
    {
        $this->projectUrl = rtrim($projectUrl, '/');
        $this->routes = [
            'GET' => [],
            'POST' => [],
            'PUT' => [],
            'PATCH' => [],
            'DELETE' => [],
            'OPTIONS' => []
        ];
    }

    /**
     * @param mixed $container A PSR-11 container compatible object
     * @return Router
     */
    public function setContainer(mixed $container): Router
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @param string $namespace
     * @return Router
     */
    public function namespace(string $namespace): Router
    {
        $this->namespace = rtrim($namespace, '\\');
        return $this;
    }

    /**
     * @param string|null $group
     * @return Router
     */
    public function group(?string $group): Router
    {
        $this->group = $group ? '/' . trim($group, '/') : null;
        return $this;
    }

    /**
     * @param string $route
     * @param string|Closure $handler
     * @return Route
     */
    public function get(string $route, string|Closure $handler): Route
    {
        return $this->addRoute('GET', $route, $handler);
    }

    /**
     * @param string $route
     * @param string|Closure $handler
     * @return Route
     */
    public function post(string $route, string|Closure $handler): Route
    {
        return $this->addRoute('POST', $route, $handler);
    }
    
    /**
     * @param string $route
     * @param string|Closure $handler
     * @return Route
     */
    public function put(string $route, string|Closure $handler): Route
    {
        return $this->addRoute('PUT', $route, $handler);
    }
    
    /**
     * @param string $route
     * @param string|Closure $handler
     * @return Route
     */
    public function patch(string $route, string|Closure $handler): Route
    {
        return $this->addRoute('PATCH', $route, $handler);
    }
    
    /**
     * @param string $route
     * @param string|Closure $handler
     * @return Route
     */
    public function delete(string $route, string|Closure $handler): Route
    {
        return $this->addRoute('DELETE', $route, $handler);
    }

    /**
     * @param string $method
     * @param string $route
     * @param string|Closure $handler
     * @return Route
     */
    private function addRoute(string $method, string $route, string|Closure $handler): Route
    {
        $formattedRoute = '/' . trim($route, '/');
        
        if ($this->group) {
            $formattedRoute = $this->group . ($formattedRoute === '/' ? '' : $formattedRoute);
        }

        $formattedRoute = '/' . trim($formattedRoute, '/');

        if (is_string($handler) && $this->namespace) {
            $handler = $this->namespace . '\\' . ltrim($handler, '\\');
        }

        $routeObject = new Route($method, $formattedRoute, $handler);
        $routeObject->setGroup($this->group);
        
        $this->routes[$method][$formattedRoute] = $routeObject;

        return $routeObject;
    }
    
    /**
     * Find matching route returning structured result
     * @param string|null $method
     * @param string|null $uri
     * @return RouteMatch
     */
    public function match(?string $method = null, ?string $uri = null): RouteMatch
    {
        $method = $method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $uri ?? $_SERVER['REQUEST_URI'] ?? '/';
        
        $dispatch = new Dispatch($this->projectUrl, $this->routes);
        return $dispatch->match($method, $uri);
    }

    /**
     * Backwards compatible dispatch
     * Note: for frameworks, prefer using ->match() directly to take control of execution
     * @param callable|null $context An optional object/context (like Request) injected into the handler
     * @return bool
     */
    public function dispatch(mixed $context = null): bool
    {
        $this->error = null;
        
        try {
            $match = $this->match();
            
            if (!$match->isSuccess()) {
                if ($match->getError() === 405) {
                    header("Allow: " . implode(", ", $match->getAllowedMethods()));
                }
                throw new Exception($match->getError() === 405 ? "Method not allowed" : "Route not found", $match->getError());
            }

            return $this->execute($match->getRoute()->getHandler(), $match->getParams(), $context);
        } catch (Exception $e) {
            $this->error = (string)$e->getCode() ?: "500";
            return false;
        }
    }

    /**
     * @param mixed $handler
     * @param array $params
     * @param mixed $context Optional context like Request
     * @return bool
     * @throws Exception
     */
    private function execute(mixed $handler, array $params, mixed $context = null): bool
    {
        if ($handler instanceof Closure) {
            if ($context !== null) {
                call_user_func_array($handler, [$context, $params]);
            } else {
                call_user_func_array($handler, [$params]);
            }
            return true;
        }

        if (is_string($handler)) {
            $segments = explode(':', $handler);
            if (count($segments) !== 2) {
                throw new Exception("Invalid handler format. Expected Class:method");
            }
            
            list($controllerName, $method) = $segments;
            
            $controller = null;
            
            if ($this->container !== null && method_exists($this->container, 'has') && $this->container->has($controllerName)) {
                $controller = $this->container->get($controllerName);
            } else {
                if (!class_exists($controllerName)) {
                    throw new Exception("Controller {$controllerName} not found");
                }
                $controller = new $controllerName();
            }
            
            if (!method_exists($controller, $method)) {
                throw new Exception("Method {$method} not found in {$controllerName}");
            }
            
            if ($context !== null) {
                call_user_func_array([$controller, $method], [$context, $params]);
            } else {
                call_user_func_array([$controller, $method], [$params]);
            }
            
            return true;
        }
        
        throw new Exception("Invalid handler");
    }

    /**
     * @return string|null
     */
    public function error(): ?string
    {
        return $this->error;
    }
    
    /**
     * @param string $route
     */
    public function redirect(string $route): void
    {
        header("Location: " . $this->projectUrl . "/" . ltrim($route, '/'));
        exit;
    }
}
