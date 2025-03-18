<?php

use PHPUnit\Framework\TestCase;
use Router\Route;

class RouteTest extends TestCase
{
    public function testAddFilter()
    {
        // Arrange
        $route = new Route();
        $filter = function($request, $response) {
            // Example filter logic
        };

        // Act
        $route->addFilter($filter);

        // Assert
        $this->assertContains($filter, $route->getFilters());
    }
}
