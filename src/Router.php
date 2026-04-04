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
     * @return Router
     */
    public function get(string $route, string|Closure $handler): Router
    {
        return $this->addRoute('GET', $route, $handler);
    }

    /**
     * @param string $route
     * @param string|Closure $handler
     * @return Router
     */
    public function post(string $route, string|Closure $handler): Router
    {
        return $this->addRoute('POST', $route, $handler);
    }
    
    /**
     * @param string $route
     * @param string|Closure $handler
     * @return Router
     */
    public function put(string $route, string|Closure $handler): Router
    {
        return $this->addRoute('PUT', $route, $handler);
    }
    
    /**
     * @param string $route
     * @param string|Closure $handler
     * @return Router
     */
    public function patch(string $route, string|Closure $handler): Router
    {
        return $this->addRoute('PATCH', $route, $handler);
    }
    
    /**
     * @param string $route
     * @param string|Closure $handler
     * @return Router
     */
    public function delete(string $route, string|Closure $handler): Router
    {
        return $this->addRoute('DELETE', $route, $handler);
    }

    /**
     * @param string $method
     * @param string $route
     * @param string|Closure $handler
     * @return Router
     */
    private function addRoute(string $method, string $route, string|Closure $handler): Router
    {
        $formattedRoute = '/' . trim($route, '/');
        
        if ($this->group) {
            $formattedRoute = $this->group . ($formattedRoute === '/' ? '' : $formattedRoute);
        }

        $formattedRoute = '/' . trim($formattedRoute, '/');

        if (is_string($handler) && $this->namespace) {
            $handler = $this->namespace . '\\' . ltrim($handler, '\\');
        }

        $this->routes[$method][$formattedRoute] = $handler;

        return $this;
    }

    /**
     * @return bool
     */
    public function dispatch(): bool
    {
        $this->error = null;
        
        try {
            $dispatch = new Dispatch($this->projectUrl, $this->routes);
            return $dispatch->run();
        } catch (Exception $e) {
            $this->error = (string)$e->getCode() ?: "500";
            return false;
        }
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
