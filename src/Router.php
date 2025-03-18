<?php

namespace Router;

use Loader\Container;
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
        'other' => []
    ];

    private static $onError = null;

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
        self::innerAdd($method, $route, $expression, $name, $filter);
    }

    /**
     * Add a route.
     *
     * @param  Route $route
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
            self::addRoute($route);
        }
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
     * @param bool $caseSensitive does the URL is case sensitive or not
     *
     * @return mixed
     */
    public static function run(bool $caseSensitive = false)
    {
        $parsedUrl = parse_url($_SERVER['REQUEST_URI']);
        $path = $parsedUrl['path'] ?? '/';
        $path = urldecode($path);
        $reqMethod = strtolower($_SERVER['REQUEST_METHOD']);

        return self::handleRequest($path, $reqMethod, $caseSensitive);
    }

    // /**
    //  * Runs the current api route
    //  *
    //  * @param bool $caseSensitive does the URL is case sensitive or not
    //  *
    //  * @return void
    //  */
    // public static function runApi(bool $caseSensitive = false)
    // {
    //     $parsedUrl = parse_url($_SERVER['REQUEST_URI']);
    //     $path = $parsedUrl['path'] ?? '/';
    //     $path = explode('/', ltrim(urldecode($path), '/'));
    //     $reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
    //     $controllerName = "App\Controller\\" . ucfirst($path[1]) . 'Controller';
    //     $controllerObj = new $controllerName();
    //     unset($path[0]);
    //     unset($path[1]);
    //     switch ($reqMethod) {
    //         case self::METHOD_GET:
    //             $controllerObj->get(...$path);
    //             break;
    //         case self::METHOD_POST:
    //             $controllerObj->create(...$path);
    //             break;
    //         case self::METHOD_PUT:
    //             $controllerObj->update(...$path);
    //             break;
    //         case self::METHOD_PATCH:
    //             $controllerObj->patch(...$path);
    //             break;
    //         case self::METHOD_DELETE:
    //             $controllerObj->delete(...$path);
    //             break;
    //         default:
    //             echo 'Invalid Request';
    //             exit();
    //     }
    // }

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

            if (!$caseSensitive) {
                $routeUrl = $routeUrl . 'i';
            }
            if (preg_match($routeUrl, $path, $matches)) {
                array_shift($matches);

                return self::runRoute($route);
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
            return self::runRoute($error_handler);
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
        $route = self::frameRoute($method, $route, $expression, $name, $filter);
        $route_key = $route->getName();
        if (! empty($route_key)) {
            self::$routes[$method][$name] = $route;
        }

        self::$routes[$method][] = $route;
    }

    /**
     * Frame the route
     *
     * @param string $method     method
     * @param mixed  $rule       rule
     * @param mixed  $expression expression
     * @param string $name       name
     * @param array  $filter     filter
     *
     * @return Route
     */
    public static function frameRoute(string $method, $rule, $expression, string $name = '', array $filter = [])
    {
        $route = new Route();
        $route->setName($name)
            ->setPath($rule)
            ->setExpression($expression)
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
     * @return Route
     */
    private static function setUpRoute(Route $route, array $params = []): Route
    {
        $request = null;
        $response = null;

        foreach ($params as $param) {
            if ($param instanceof Request) {
                $request = $param;
            } elseif ($param instanceof Response) {
                $response = $param;
            }

            // Break early if both request and response are found
            if ($request && $response) {
                break;
            }
        }

        // Use existing request/response instances if provided, else default to generated ones
        $route->setRequest($request ?? self::getRequest($route));
        $route->setResponse($response ?? self::getResponse());

        $route->handleFilters();

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

        return array_merge($ctrl_params, $action_params);
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
        $action = $route->getAction();
        if (is_callable($controller)) {
            self::setUpRoute($route, []);

            $result = call_user_func($controller, $route->getRequest(), $route->getResponse(), ...$route->getUrlParams());

            return self::handleControllerResult($result, $route);
        }

        //set parent request and response
        $route->setRequest(self::getRequest($route));
        $route->setResponse(self::getResponse());
        $params = self::getAllParms($route);

        $route = self::setUpRoute($route, $params);
        $ctrl_classs = Container::resolve($controller, $params);
        if (!method_exists($ctrl_classs, $action)) {
            self::error('Method not found', 404);
        }
        $args = Container::resolveMethod($controller, $action, $params);

        $result = call_user_func([$ctrl_classs, $action], ...$args);

        return self::handleControllerResult($result, $route);
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
