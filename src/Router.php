<?php

namespace Router;

use Loader\Container;
use Router\Exception\RouterException;
use Router\Request\Model\Model;
use Router\Request\Request;
use Router\Response\Response;

class Router
{
    public const METHOD_GET = 'get';

    public const METHOD_POST = 'post';

    public const METHOD_PUT = 'put';

    public const METHOD_DELETE = 'delete';

    public const METHOD_PATCH = 'patch';

    /**
     * Routes
     *
     * @var array
     */
    private static $routes = [
        self::METHOD_GET => [],
        self::METHOD_POST => [],
        self::METHOD_PUT => [],
        self::METHOD_DELETE => [],
        self::METHOD_PATCH => [],
        'other' => [],
    ];

    private static $onError = null;
    private static $prefix = '';

    /**
     * Adds new Route
     *
     * @param string      $route      route
     * @param string|null $expression execution value (controller/method)
     * @param string      $method     method Name
     * @param array       $filter     filter function
     * @param string|null $name       Route alias name
     *
     * @return void
     */
    public static function add(
        string $route,
        ?string $expression = null,
        string $method = self::METHOD_GET,
        array $filter = [],
        ?string $name = null
    ) {
        $method = strtolower($method);
        self::innerAdd($method, $route, $expression, $name ?? '', $filter);
    }

    /**
     * Add a route.
     *
     * @param Route $route
     *
     * @return void
     */
    public static function addRoute(Route $route)
    {
        $method = $route->getMethod();
        $name = $route->getName();

        self::$routes[$method][$name] = $route;
    }

    /**
     * Add multiple routes.
     *
     * @param  array $routes
     * @return void
     */
    public static function addRoutes(array $routes)
    {
        foreach ($routes as $route) {
            if ($route instanceof Route) {
                self::addRoute($route);
            }
            if ($route instanceof Wrapper) {
                self::addFromWrapper($route);
            }
        }
    }

    public static function addFromWrapper(Wrapper $wrapper)
    {
        self::addRoutes($wrapper->getRoutes());
    }

    /**
     * Returns the URL
     *
     * @param string $name   URL alias name
     * @param string $method URL method
     * @param array  $data   URL data
     *
     * @return string|null
     */
    public static function getURL(
        string $name,
        string $method = self::METHOD_GET,
        array $data = []
    ): ?string {
        $route = self::$routes[$method][$name] ?? null;
        if ($route == null) {
            return null;
        }
        $path = $route->getPath();
        foreach ($data as $value) {
            $path .= '/' . $value;
        }

        return $path;
    }

    /**
     * Sets on error method
     *
     * @param callable $callback method
     *
     * @return void
     */
    public static function setOnError(callable $callback)
    {
        self::$onError = $callback;
    }

    /**
     * Runs the current route
     *
     * @param bool        $caseSensitive does the URL is case sensitive or not
     * @param string|null $url           URL
     * @param string|null $method        method
     *
     * @return mixed
     */
    public static function run(bool $caseSensitive = false, ?string $url = null, ?string $method = null)
    {
        if (! $url) {
            $parsedUrl = parse_url($_SERVER['REQUEST_URI']);
            $url = $parsedUrl['path'] ?? '/';
        }
        $path = $url;
        $path = urldecode($path);
        $reqMethod = strtolower($method ?? $_SERVER['REQUEST_METHOD']);

        return self::handleRequest($path, $reqMethod, $caseSensitive);
    }

    /**
     * Handles the URL request
     *
     * @param string $path          Requested URL path
     * @param string $method        Requested method
     * @param bool   $caseSensitive Does the URL is case sensitive or not
     *
     * @return mixed
     */
    public static function handleRequest(
        string $path,
        string $method,
        bool $caseSensitive = false
    ) {
        foreach (self::$routes[$method] as $route) {
            $routeUrl = '#^' . $route->getRegex() . '$#';
            if (! $caseSensitive) {
                $routeUrl = $routeUrl . 'i';
            }
            if (preg_match($routeUrl, $path, $matches)) {
                array_shift($matches);

                return self::runRoute($route, $matches);
            }
        }

        return self::error('Page not found', 404);
    }

    /**
     * Calls when an error occured
     *
     * @param string|null $data Error data
     *
     * @return mixed
     */
    public static function error(?string $data = null, int $code = 500)
    {
        $error_handler = self::$onError;

        if (is_callable($error_handler)) {
            ob_start();

            return call_user_func(self::$onError, $data);
        }

        if ($error_handler instanceof Route) {
            return self::runRoute($error_handler, [$data]);
        }

        $response = new Response();
        $response->setStatusCode($code);
        $response->setBody($data);

        return $response;
    }

    /**
     * Adds new Route
     *
     * @param string      $route      route
     * @param string|null $expression execution value (controller/method)
     * @param string      $method     method Name
     * @param string      $name       Route alias name
     * @param array       $filter     filter function
     *
     * @return void
     */
    private static function innerAdd(string $method, string $route, $expression, string $name = '', $filter = [])
    {
        $route = self::frameRoute($route, $expression, $method, $filter, $name);
        $route_key = $route->getName();
        if (! empty($route_key)) {
            self::$routes[$method][$name] = $route;
        }

        self::$routes[$method][] = $route;
    }

    /**
     * Frame the route
     *
     * @param mixed  $rule       rule
     * @param mixed  $expression expression
     * @param string $method     method
     * @param array  $filter     filter
     * @param string $name       name
     *
     * @return Route
     */
    public static function frameRoute($rule, $expression, string $method = self::METHOD_GET, array $filter = [], string $name = '')
    {
        $route = new Route($rule, $expression);
        $route->setName($name)
            ->setMethod($method)
            ->setFilters($filter);

        return $route;
    }

    /**
     * Get request.
     *
     * @param Route $route
     *
     * @return Request
     */
    private static function getRequest(Route $route)
    {
        return new Request($route->getUrlParams());
    }

    /**
     * Get response.
     *
     * @return Response
     */
    private static function getResponse()
    {
        return new Response();
    }

    /**
     * Set up Route.
     *
     * @param Route $route
     * @param array $params
     *
     * @return ?Route
     */
    private static function setUpRoute(Route $route, array $params = [])
    {
        $request = null;
        $response = null;

        foreach ($params as $param) {
            if ($param instanceof Request) {
                $request = $param;
            } elseif ($param instanceof Response) {
                $response = $param;
            } elseif ($param instanceof Model) {
                $request = $route->getRequest();
                $data = $request->post();
                $data = empty($data) ? $request->data() : $data;
                $param->setValues($data);
                Container::set($param::class, $param);
            }

            // Break early if both request and response are found
            if ($request && $response) {
                break;
            }
        }

        // Use existing request/response instances if provided, else default to generated ones
        $route->setRequest($request ?? self::getRequest($route));
        $route->setResponse($response ?? self::getResponse());
        Container::set(Request::class, $route->getRequest());
        Container::set(Response::class, $route->getResponse());
        if (! $route->handleFilters()) {
            return null;
        }

        return $route;
    }

    /**
     * Get all parameters.
     *
     * @param Route $route
     *
     * @return array
     */
    private static function getAllParms(Route $route)
    {
        $controller = $route->getController();
        $action = $route->getAction();
        $url_params = self::getParamsForRoute($route);

        $ctrl_params = Container::getConstrParams($controller, $url_params);
        $action_params = Container::resolveMethod($controller, $action, $url_params);

        return array_merge($url_params, $ctrl_params, $action_params);
    }

    private static function getPrefixWithCtr($ctrl)
    {
        if (self::$prefix) {
            return self::$prefix . '\\' . $ctrl;
        }

        return $ctrl;
    }

    /**
     * Run the route.
     *
     * @param Route $route
     * @param array $matches
     *
     * @return mixed
     */
    private static function runRoute(Route $route, array $matches = [])
    {
        $route->setMatches($matches);
        $controller = $route->getController();

        if (! class_exists($controller)) {
            $controller = $controller . 'Controller';
            $route->setController($controller);
            if (! class_exists($controller)) {
                $controller = self::getPrefixWithCtr($controller);
                if (! class_exists($controller)) {
                    throw new RouterException("controller class not found : $controller", RouterException::CONTROLLER_NOT_FOUND_ERROR);
                }
                $route->setController($controller);
            }
        }
        $action = $route->getAction();
        if (is_callable($controller)) {
            if (! self::setUpRoute($route, [])) {
                return $route->getResponse();
            }
            Container::set(Request::class, $route->getRequest());
            Container::set(Response::class, $route->getResponse());
            $result = call_user_func($controller, $route->getRequest(), $route->getResponse(), ...$route->getUrlParams());

            return self::handleControllerResult($result, $route);
        }

        //set parent request and response
        $route->setRequest(self::getRequest($route));
        $route->setResponse(self::getResponse());
        $params = self::getAllParms($route);

        if (! self::setUpRoute($route, $params)) {
            return $route->getResponse();
        }
        Container::set(Request::class, $route->getRequest());
        Container::set(Response::class, $route->getResponse());
        $ctrl_classs = Container::resolve($controller, $params);
        if (! method_exists($controller, $action)) {
            self::error('Method not found', 404);
        }
        $args = Container::resolveMethod($controller, $action, $params);
        $result = call_user_func([$ctrl_classs, $action], ...$args);

        return self::handleControllerResult($result, $route);
    }

    public static function setPrefix($prefix)
    {
        self::$prefix = $prefix;
    }

    public static function getPrefix()
    {
        return self::$prefix;
    }

    /**
     * Handle the controller result.
     *
     * @param mixed $result
     * @param Route $route
     *
     * @return mixed
     */
    public static function handleControllerResult($result, Route $route)
    {
        if (! is_null($result)) {
            return $result;
        }

        return $route->getResponse();
    }

    /**
     * Get the parameters for the route.
     *
     * @param Route $route
     *
     * @return array
     */
    public static function getParamsForRoute(Route $route)
    {
        $params = [];
        foreach ($route->getUrlParams() as $key => $param) {
            $params[$key] = $param;
        }

        foreach ($route->getRequest()->get() as $key => $param) {
            $params[$key] = $param;
        }

        return $params;
    }
}
