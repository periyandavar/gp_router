<?php

use PHPUnit\Framework\TestCase;
use Router\Filter\Filter;
use Router\Request\Request;
use Router\Response\Response;
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

    public function testsetGetPrefix()
    {
        $route = new Route('', 'aa');
        $route->setPrefix('aa');
        $this->assertEquals('aa', $route->getPrefix());
    }

    public function testSetUrlParamsAndGetUrlParams()
    {
        $route = new Route('/foo', 'bar/action');
        $params = ['id' => 123, 'slug' => 'test'];
        $route->setUrlParams($params);
        $this->assertEquals($params, $route->getUrlParams());
    }

    public function testSetMatches()
    {
        $route = new Route('/foo/{(\d+):id}/{(\w+):slug}', 'bar/action');
        // Simulate urlKeys extraction
        $reflection = new ReflectionClass($route);
        $urlKeysProp = $reflection->getProperty('urlKeys');
        $urlKeysProp->setAccessible(true);
        $urlKeysProp->setValue($route, ['id', 'slug']);

        $route->setMatches([42, 'hello']);
        $this->assertEquals(['id' => 42, 'slug' => 'hello'], $route->getUrlParams());
    }

    public function testHandleFiltersWithCallable()
    {
        $route = new Route('/foo', 'bar/action');
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $route->setRequest($request);
        $route->setResponse($response);

        $route->setFilters([
            function($req, $res) { return true; },
        ]);
        $this->assertTrue($route->handleFilters());

        $route->setFilters([
            function($req, $res) { return false; },
        ]);
        $this->assertFalse($route->handleFilters());
    }

    public function testHandleFiltersWithFilterInstance()
    {
        $route = new Route('/foo', 'bar/action');
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $route->setRequest($request);
        $route->setResponse($response);

        // Create a mock Filter that returns true
        $filterMock = $this->createMock(Filter::class);
        $filterMock->method('filter')->willReturn(true);

        $route->setFilters([$filterMock]);
        $this->assertTrue($route->handleFilters());

        // Now test with a Filter that returns false
        $filterMockFalse = $this->createMock(Filter::class);
        $filterMockFalse->method('filter')->willReturn(false);

        $route->setFilters([$filterMockFalse]);
        $this->assertFalse($route->handleFilters());
    }

    public function testExpression()
    {
        $route = new Route('', '');
        $route->setExpression(['r' => 'a']);
        $this->assertEquals(['r' => 'a'], $route->getExpression());
    }

    public function testHandleFiltersWithStringFilter()
    {
        // Define a filter class with __invoke and filter methods
        $filterClass = new class() {
            public function filter($req, $res)
            {
                return true;
            }
        };
        $filterClassName = get_class($filterClass);

        $route = new Route('/foo', 'bar/action');
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $route->setRequest($request);
        $route->setResponse($response);

        // Set the filter as a string (class name)
        $route->setFilters([
            $filterClassName,
        ]);
        $this->assertTrue($route->handleFilters());
    }
}
