<?php

namespace Needinfo\Router;

class Dispatch
{
    private string $projectUrl;
    private array $routes;

    /**
     * @param string $projectUrl
     * @param array $routes
     */
    public function __construct(string $projectUrl, array $routes)
    {
        $this->projectUrl = rtrim($projectUrl, '/');
        $this->routes = $routes;
    }

    /**
     * @param string $method
     * @param string $uri
     * @return RouteMatch
     */
    public function match(string $method, string $uri): RouteMatch
    {
        $method = strtoupper($method);
        
        $basePath = parse_url($this->projectUrl, PHP_URL_PATH) ?? '';
        
        $path = filter_var($uri, FILTER_SANITIZE_URL);
        
        if ($basePath && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }
        
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        
        $path = '/' . trim($path, '/');

        // Look for match in current method
        $match = $this->matchInMethod($method, $path);
        
        if ($match instanceof RouteMatch) {
            return $match;
        }

        // Verify if route exists in other methods (405 Method Not Allowed)
        $allowedMethods = [];
        foreach (array_keys($this->routes) as $iterMethod) {
            if ($iterMethod !== $method && $this->matchInMethod($iterMethod, $path, true)) {
                $allowedMethods[] = $iterMethod;
            }
        }

        if (!empty($allowedMethods)) {
            return new RouteMatch(null, [], 405, $allowedMethods);
        }

        return new RouteMatch(null, [], 404);
    }

    /**
     * @param string $method
     * @param string $path
     * @param bool $checkOnly
     * @return RouteMatch|bool
     */
    private function matchInMethod(string $method, string $path, bool $checkOnly = false): RouteMatch|bool
    {
        if (empty($this->routes[$method])) {
            return false;
        }

        /** @var Route $route */
        foreach ($this->routes[$method] as $routePath => $route) {
            $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)(?::([^\}]+))?\}/', function ($m) {
                $name = $m[1];
                $constraint = $m[2] ?? '[^/]+';
                return "(?P<{$name}>{$constraint})";
            }, $routePath);

            $pattern = str_replace('/', '\/', $pattern);
            
            if (preg_match('/^' . $pattern . '$/', $path, $matches)) {
                if ($checkOnly) {
                    return true;
                }
                
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = rawurldecode($value);
                    }
                }
                
                return new RouteMatch($route, $params, 0);
            }
        }
        
        return false;
    }
}
