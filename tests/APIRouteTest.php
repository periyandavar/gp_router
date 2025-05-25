<?php

use PHPUnit\Framework\TestCase;
use Router\APIRoute;
use Router\Route;

class APIRouteTest extends TestCase
{
    public function testGetRestAPIActions()
    {
        // Act
        $actions = APIRoute::getRestAPIActions();

        // Assert
        $expectedActions = [
            APIRoute::ACTION_LIST,
            APIRoute::ACTION_VIEW,
            APIRoute::ACTION_CREATE,
            APIRoute::ACTION_UPDATE,
            APIRoute::ACTION_DELETE,
        ];
        $this->assertEquals($expectedActions, $actions);
    }

    public function testAddAction()
    {
        // Arrange
        $rule = '/api/resource';
        $expression = 'ResourceController';
        $name = 'resource';

        // Create an instance of APIRoute
        $apiRoute = new APIRoute($rule, $expression, [], [], $name);

        // Act

        // Use reflection to access the private $routes property
        $reflection = new \ReflectionClass($apiRoute);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($apiRoute);

        // Assert
        $this->assertCount(5, $routes);
        // $this->assertInstanceOf(Route::class, $routes[0]);
        // $this->assertEquals($rule, $routes[0]->getRoute());
        // $this->assertEquals('ResourceController/create', $routes[0]->getExpression());
        // $this->assertEquals('post', $routes[0]->getMethod());
        // $this->assertEquals($filters, $routes[0]->getFilters());
        // $this->assertEquals($name, $routes[0]->getName());
    }
}
