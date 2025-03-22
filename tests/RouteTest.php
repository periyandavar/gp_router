<?php

use PHPUnit\Framework\TestCase;
use Router\Route;
use Router\Router;

class RouteTest extends TestCase
{
    public function testAddFilter()
    {
        // Arrange
        $route = new Route('', '');
        $filter = function($request, $response) {
            // Example filter logic
        };

        // Act
        $route->addFilter($filter);

        // Assert
        $this->assertContains($filter, $route->getFilters());
    }

    public function testAddRoutes()
    {
        // Arrange
        $route1 = $this->createMock(Route::class);
        $route1->method('getMethod')->willReturn(Router::METHOD_GET);
        $route1->method('getName')->willReturn('route1');

        $route2 = $this->createMock(Route::class);
        $route2->method('getMethod')->willReturn(Router::METHOD_POST);
        $route2->method('getName')->willReturn('route2');

        $routes = [$route1, $route2];

        // Act
        Router::addRoutes($routes);

        // Use reflection to access the private $routes property
        $reflection = new \ReflectionClass(Router::class);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $actualRoutes = $routesProperty->getValue();

        // Assert
        $this->assertArrayHasKey('route1', $actualRoutes[Router::METHOD_GET]);
        $this->assertArrayHasKey('route2', $actualRoutes[Router::METHOD_POST]);
    }
}
