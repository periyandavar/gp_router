<?php

namespace Router;

class Wrapper
{
    /**
     * @var Route[]
     */
    protected $routes = [];

    /**
     * Get the value of routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set the value of routes
     */
    public function setRoutes($routes): self
    {
        $this->routes = $routes;

        return $this;
    }
}
