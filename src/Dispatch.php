<?php

namespace Needinfo\Router;

use Closure;
use Exception;

class Dispatch
{
    private string $projectUrl;
    private string $requestMethod;
    private string $requestUrl;
    private array $routes;

    /**
     * @param string $projectUrl
     * @param array $routes
     */
    public function __construct(string $projectUrl, array $routes)
    {
        $this->projectUrl = rtrim($projectUrl, '/');
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->routes = $routes;
        
        // Remove project base url from request url
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $path = filter_var($path, FILTER_SANITIZE_URL);
        
        $basePath = parse_url($this->projectUrl, PHP_URL_PATH) ?? '';
        
        if ($basePath && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }
        
        // Remove query strings
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        
        $this->requestUrl = '/' . trim($path, '/');
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function run(): bool
    {
        if (empty($this->routes[$this->requestMethod])) {
            throw new Exception("Method not allowed", 405);
        }

        foreach ($this->routes[$this->requestMethod] as $route => $handler) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_\-]+)', $route);
            $pattern = str_replace('/', '\/', $pattern);
            
            if (preg_match('/^' . $pattern . '$/', $this->requestUrl, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                
                return $this->execute($handler, $params);
            }
        }
        
        throw new Exception("Route not found", 404);
    }

    /**
     * @param mixed $handler
     * @param array $params
     * @return bool
     * @throws Exception
     */
    private function execute(mixed $handler, array $params): bool
    {
        if ($handler instanceof Closure) {
            call_user_func_array($handler, [$params]);
            return true;
        }

        if (is_string($handler)) {
            $segments = explode(':', $handler);
            if (count($segments) !== 2) {
                throw new Exception("Invalid handler format. Expected Class:method");
            }
            
            list($controllerName, $method) = $segments;
            
            if (!class_exists($controllerName)) {
                throw new Exception("Controller {$controllerName} not found");
            }
            
            $controller = new $controllerName();
            
            if (!method_exists($controller, $method)) {
                throw new Exception("Method {$method} not found in {$controllerName}");
            }
            
            call_user_func_array([$controller, $method], [$params]);
            return true;
        }
        
        throw new Exception("Invalid handler");
    }
}
