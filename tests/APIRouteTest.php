<?php

use PHPUnit\Framework\TestCase;
use Router\APIRoute;
use Router\Route;
use Router\Router;

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

        $rule = '/api/resource1/<(\d+):name>';
        $expression = 'ResourceController';
        $name = 'resource';

        $apiRoute = new APIRoute($rule, $expression, [], [APIRoute::ACTION_CREATE]);
        $routes = $routesProperty->getValue($apiRoute);
        $this->assertCount(4, $routes);

    }
}
