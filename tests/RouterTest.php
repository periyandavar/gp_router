<?php

use PHPUnit\Framework\TestCase;
use Router\Exception\RouterException;
use Router\Request\Model\Model;
use Router\Request\Request;
use Router\Response\Response;
use Router\Route;
use Router\Router;
use Router\Wrapper;

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
        $url = Router::getURL('testRoute', Router::METHOD_GET, [1]);
        $this->assertEquals('/test/1', $url);
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testSetPrefix()
    {
        Router::setPrefix('test');
        $this->assertEquals('test', Router::getPrefix());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testAddFromWrapper()
    {
        $wrapper = new Wrapper();
        $route = new Route('', function() {}, 'get', [], 'test');
        $wrapper->setRoutes([$route]);
        Router::addRoutes([$wrapper]);
        $this->assertEquals('', Router::getURL('test'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled.
     *
     * @return void
     */
    public function testError()
    {
        $obLevel = ob_get_level();

        Router::setOnError(function() {return 'error';});
        $result = Router::run();
        $this->assertEquals('error', $result);
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function testGetUrlNull()
    {
        $this->assertNull(Router::getURL('test'));
    }

    public function testRunRouteThrowsExceptionIfControllerNotFound()
    {
        $route = new Route('/test', 'NonExistentController@action');
        $route->setController('NonExistent'); // Will be appended with 'Controller'
        // $route->setAction('action');

        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('controller class not found');

        // runRoute is private, so we use Reflection to call it
        $reflection = new ReflectionClass(Router::class);
        $method = $reflection->getMethod('runRoute');
        $method->setAccessible(true);

        $method->invokeArgs(null, [$route, []]);
    }

    public function testRunRouteReturnsResponseIfSetUpRouteFails()
    {
        // Use a non-callable controller (string)
        $route = new Route('/test', 'NonCallableController');
        // Optionally set other route properties as needed

        // Use Reflection to call runRoute
        $routerReflection = new ReflectionClass(Router::class);
        $runRoute = $routerReflection->getMethod('runRoute');
        $runRoute->setAccessible(true);
        $this->expectException(RouterException::class);
        // Call runRoute and assert it returns the route's response
        $result = $runRoute->invokeArgs(null, [$route, []]);
    }

    public function testRunRouteCallsControllerAndHandlesResult()
    {
        $called = false;
        $controller = function($req, $res) use (&$called) {
            $called = true;

            return 'controller-result';
        };

        $route = new Route('/test', $controller);
        $route->setController($controller);
        // $route->setAction('__invoke');
        // $route->setRequest(new Request([]));
        // $route->setResponse(new Response());

        // Use Reflection to call runRoute
        $routerReflection = new ReflectionClass(Router::class);
        $runRoute = $routerReflection->getMethod('runRoute');
        $runRoute->setAccessible(true);

        $result = $runRoute->invokeArgs(null, [$route, []]);
        $this->assertEquals('controller-result', $result);
        $this->assertTrue($called);
    }

    public function testGetPrefixWithCtrReturnsPrefixedController()
    {
        // Set the prefix
        Router::setPrefix('Admin');
        $controller = 'UserController';
        $result = $this->callPrivateMethod(Router::class, 'getPrefixWithCtr', [$controller]);
        $this->assertEquals('Admin\\UserController', $result);

        // Reset prefix and test without prefix
        Router::setPrefix('');
        $result = $this->callPrivateMethod(Router::class, 'getPrefixWithCtr', [$controller]);
        $this->assertEquals('UserController', $result);
    }

    public function testHandleControllerResultReturnsResultIfNotNull()
    {
        $route = $this->createMock(Route::class);
        $result = 'some value';
        $actual = Router::handleControllerResult($result, $route);
        $this->assertEquals('some value', $actual);
    }

    public function testHandleControllerResultReturnsRouteResponseIfNull()
    {
        $mockResponse = $this->createMock(Response::class);
        $route = $this->createMock(Route::class);
        $route->method('getResponse')->willReturn($mockResponse);

        $actual = Router::handleControllerResult(null, $route);
        $this->assertSame($mockResponse, $actual);
    }

    private function callPrivateMethod($class, $method, array $args = [])
    {
        $ref = new ReflectionClass($class);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);

        return $m->invokeArgs(null, $args);
    }

    public function testGetParamsForRoute()
    {
        // Create a Route with a controller expecting parameters
        $route = new Route('/test/{id}/{slug}', 'TestController@testMethod');
        $route->setUrlParams(['id' => 42, 'slug' => 'hello']);

        // Simulate a request and response
        $request = new Request(['t' => '']);
        $response = new Response();
        $route->setRequest($request);
        $route->setResponse($response);

        // Use Reflection to call the private/protected getParamsForRoute method
        $reflection = new ReflectionClass(Router::class);
        $method = $reflection->getMethod('getParamsForRoute');
        $method->setAccessible(true);

        $params = $method->invokeArgs(null, [$route]);

        // The params should include the request, response, and url params in order
        $this->assertContains(42, $params);
        $this->assertContains('hello', $params);
    }
}

class TestController
{
    public function testMethod(Request $req, Response $res, TestModel $model)
    {
        return 'Test Method';
    }
}

class TestModel implements Model
{
    public function setValues(array $values)
    {
    }

    public function getValues(): array
    {
        return [];
    }
}
