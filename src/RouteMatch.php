<?php

namespace Needinfo\Router;

/**
 * Class RouteMatch
 * @package Needinfo\Router
 */
class RouteMatch
{
    private ?Route $route;
    private array $params;
    private int $error;
    private array $allowedMethods;

    /**
     * RouteMatch constructor.
     * @param Route|null $route
     * @param array $params
     * @param int $error
     * @param array $allowedMethods
     */
    public function __construct(?Route $route = null, array $params = [], int $error = 0, array $allowedMethods = [])
    {
        $this->route = $route;
        $this->params = $params;
        $this->error = $error;
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * Returns true if route matched successfully (no errors)
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->error === 0 && $this->route !== null;
    }

    public function getRoute(): ?Route { return $this->route; }
    public function getParams(): array { return $this->params; }
    public function getError(): int { return $this->error; }
    public function getAllowedMethods(): array { return $this->allowedMethods; }
}
