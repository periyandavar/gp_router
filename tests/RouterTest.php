<?php

use PHPUnit\Framework\TestCase;
use Router\Router;

class RouterTest extends TestCase
{
    public function testAddRoute()
    {
        Router::add('/test', 'TestController/testMethod', Router::METHOD_GET, [], 'testRoute');
        $routes = (new ReflectionClass(Router::class))->getStaticProperties()['routes'];
        $this->assertArrayHasKey('testRoute', $routes[Router::METHOD_GET]);
        $this->assertEquals('/test', $routes[Router::METHOD_GET]['testRoute']->getPath());
    }

    public function testGetURL()
    {
        Router::add('/test', 'TestController@testMethod', Router::METHOD_GET, [], 'testRoute');
        $url = Router::getURL('testRoute', Router::METHOD_GET);
        $this->assertEquals('/test', $url);
    }

    public function testHandleRequestWithValidRoute()
    {
        Router::add('/test', 'TestController/testMethod', Router::METHOD_GET, [], 'testRoute');
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $result = Router::run();
        $this->assertNotNull($result);
    }

    public function testHandleRequestWithInvalidRoute()
    {
        $_SERVER['REQUEST_URI'] = '/invalid';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $result = Router::run();
        $this->assertEquals(404, $result->getStatusCode());
        $this->assertEquals('Page not found', $result->getBody());
    }
}

class TestController
{
    public function testMethod()
    {
        return 'Test Method';
    }
}
