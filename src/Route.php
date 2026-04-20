<?php

namespace Needinfo\Router;

use Closure;

/**
 * Class Route
 * @package Needinfo\Router
 */
class Route
{
    private string $method;
    private string $path;
    private string|Closure $handler;
    private ?string $name = null;
    private ?string $group = null;
    private array $middlewares = [];
    private array $metadata = [];

    /**
     * Route constructor.
     * @param string $method
     * @param string $path
     * @param string|Closure $handler
     */
    public function __construct(string $method, string $path, string|Closure $handler)
    {
        $this->method = $method;
        $this->path = '/' . ltrim($path, '/');
        $this->handler = $handler;
    }

    /**
     * Set route name
     * @param string $name
     * @return Route
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Add middleware(s) to route
     * @param string|array $middleware
     * @return Route
     */
    public function middleware(string|array $middleware): self
    {
        if (is_string($middleware)) {
            $this->middlewares[] = $middleware;
        } else {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        }
        return $this;
    }

    /**
     * Add metadata to route
     * @param array $metadata
     * @return Route
     */
    public function with(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    /**
     * @internal
     * @param string|null $group
     * @return Route
     */
    public function setGroup(?string $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getMethod(): string { return $this->method; }
    public function getPath(): string { return $this->path; }
    public function getHandler(): string|Closure { return $this->handler; }
    public function getName(): ?string { return $this->name; }
    public function getGroup(): ?string { return $this->group; }
    public function getMiddlewares(): array { return $this->middlewares; }
    public function getMetadata(): array { return $this->metadata; }
}
