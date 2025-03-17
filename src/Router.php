<?php

namespace System\Core;

class Router
{
    public const METHOD_GET = 'get';

    public const METHOD_POST = 'post';

    public const METHOD_PUT = 'put';

    public const METHOD_DELETE = 'delete';

    public const METHOD_PATCH = 'patch';
    /**
     * GET method Routes
     *
     * @var array
     */
    private static $getMethodRoutes = [];

    /**
     * POST method routes
     *
     * @var array
     */
    private static $postMethodRoutes = [];

    /**
     * PUT method routes
     *
     * @var array
     */
    private static $putMethodRoutes = [];

    /**
     * PATCH method routes
     *
     * @var array
     */
    private static $patchMethodRoutes = [];

    /**
     * DELETE method routes
     *
     * @var array
     */
    private static $deleteMethodRoutes = [];

    private static $otherMethodRoutes = [];

    /**
     * Other method routes
     *
     * @var array
     */
    private static $_otherRoutes = [];

    private static $methodNotAllowed = null;

    private static $pathNotFound = null;

    private static $onError = null;

    /**
     * Adds new Route
     *
     * @param string        $route      route
     * @param string|null   $expression execution value (controller/method)
     * @param string        $method     method Name
     * @param callable|null $filter     filter function
     * @param string|null   $name       Route alias name
     *
     * @return void
     */
    public static function add(
        string $route,
        ?string $expression = null,
        string $method = self::METHOD_GET,
        ?callable $filter = null,
        ?string $name = null
    ) {
        $method = strtolower($method);
        switch ($method) {
            case self::METHOD_GET:
                $name != null
                    ? self::$getMethodRoutes[$name] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ]
                    : self::$getMethodRoutes[] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ];
                break;

            case self::METHOD_POST:
                $name != null
                    ? self::$postMethodRoutes[$name] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ]
                    : self::$postMethodRoutes[] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ];
                break;
            case self::METHOD_PUT:
                $name != null
                    ? self::$putMethodRoutes[$name] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ]
                    : self::$putMethodRoutes[] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ];
                break;
            case self::METHOD_PATCH:
                $name != null
                    ? self::$patchMethodRoutes[$name] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ]
                    : self::$patchMethodRoutes[] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ];
                break;
            case self::METHOD_DELETE:
                $name != null
                    ? self::$deleteMethodRoutes[$name] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ]
                    : self::$deleteMethodRoutes[] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ];
                break;
            default:
                $name != null
                    ? self::$_otherRoutes[$name] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ]
                    : self::$_otherRoutes[] = [
                        'route' => $route,
                        'expression' => $expression,
                        'rule' => $filter
                        ];
                break;
        }
    }

    /**
     * Returns the URL
     *
     * @param string     $name   URL alias name
     * @param string     $method URL method
     * @param array|null $data   URL data
     *
     * @return string|null
     */
    public static function getURL(
        string $name,
        string $method = 'get',
        array $data = null
    ): ?string {
        $route = null;
        switch ($method) {
            case self::METHOD_GET:
                $route = isset(self::$getMethodRoutes[$name])
                    ? self::$getMethodRoutes[$name]['route']
                    : null;
                break;
            case self::METHOD_POST:
                $route = isset(self::$postMethodRoutes[$name])
                    ? self::$postMethodRoutes[$name]['route']
                    : null;
                break;
            case self::METHOD_PUT:
                $route = isset(self::$putMethodRoutes[$name])
                    ? self::$putMethodRoutes[$name]['route']
                    : null;
                break;
            case self::METHOD_PATCH:
                $route = isset(self::$patchMethodRoutes[$name])
                    ? self::$patchMethodRoutes[$name]['route']
                    : null;
                break;
            case self::METHOD_DELETE:
                $route = isset(self::$deleteMethodRoutes[$name])
                    ? self::$deleteMethodRoutes[$name]['route']
                    : null;
                break;
            default:
                $route = isset(self::$_otherRoutes[$name])
                    ? self::$_otherRoutes[$name]['route']
                    : null;
                break;
        }
        if ($route != null) {
            foreach ($data as $value) {
                $route .= '/' . $value;
            }
        }

        return $route;
    }

    /**
     * Sets not allowed method
     *
     * @param callable $callback method
     *
     * @return void
     */
    public static function setMethodNotAllowed(callable $callback)
    {
        self::$methodNotAllowed = $callback;
    }

    /**
     * Sets path not found method
     *
     * @param callable $callback method
     *
     * @return void
     */
    public static function setPathNotFound(callable $callback)
    {
        self::$pathNotFound = $callback;
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
     * @return void
     */
    public static function run(bool $caseSensitive = false)
    {
        $parsedUrl = parse_url($_SERVER['REQUEST_URI']);
        $path = $parsedUrl['path'] ?? '/';
        $path = urldecode($path);
        $reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        switch ($reqMethod) {
            case self::METHOD_GET:
                self::handleRequest($path, self::$getMethodRoutes, $caseSensitive);
                break;
            case self::METHOD_POST:
                self::handleRequest($path, self::$postMethodRoutes, $caseSensitive);
                break;
            case self::METHOD_PUT:
                self::handleRequest($path, self::$putMethodRoutes, $caseSensitive);
                break;
            case self::METHOD_PATCH:
                self::handleRequest($path, self::$patchMethodRoutes, $caseSensitive);
                break;
            case self::METHOD_DELETE:
                self::handleRequest($path, self::$deleteMethodRoutes, $caseSensitive);
                break;
            default:
                self::handleRequest($path, self::$otherMethodRoutes, $caseSensitive);
                break;
        }
    }

    /**
     * Runs the current api route
     *
     * @param bool $caseSensitive does the URL is case sensitive or not
     *
     * @return void
     */
    public static function runApi(bool $caseSensitive = false)
    {
        $parsedUrl = parse_url($_SERVER['REQUEST_URI']);
        $path = $parsedUrl['path'] ?? '/';
        $path = explode('/', ltrim(urldecode($path), '/'));
        $reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $controllerName = "App\Controller\\" . ucfirst($path[1]) . 'Controller';
        $controllerObj = new $controllerName();
        unset($path[0]);
        unset($path[1]);
        switch ($reqMethod) {
            case self::METHOD_GET:
                $controllerObj->get(...$path);
                break;
            case self::METHOD_POST:
                $controllerObj->create(...$path);
                break;
            case self::METHOD_PUT:
                $controllerObj->update(...$path);
                break;
            case self::METHOD_PATCH:
                $controllerObj->patch(...$path);
                break;
            case self::METHOD_DELETE:
                $controllerObj->delete(...$path);
                break;
            default:
                echo 'Invalid Request';
                exit();
        }
    }

    /**
     * Handles the URL request
     *
     * @param string $path          Requested URL path
     * @param array  $routes        Routes
     * @param bool   $caseSensitive Does the URL is case sensitive or not
     *
     * @return void
     */
    public static function handleRequest(
        string $path,
        array $routes,
        bool $caseSensitive = false
    ) {
        $pathMatch = false;
        $methodMatch = false;
        global $config;
        foreach ($routes as $route) {
            $routeUrl = '#^' . $route['route'] . '$#';

            if (!$caseSensitive) {
                $routeUrl = $routeUrl . 'i';
            }
            if (preg_match($routeUrl, $path, $matches)) {
                $pathMatch = true;
                $rule = $route['rule'];
                if ($rule != null) {
                    if ($rule($matches) != true) {
                        return;
                    }
                }
                array_shift($matches);
                $requestCtrl = $route['expression'] ?? $path;
                $requestCtrl = explode('/', trim($requestCtrl, '/'));
                $ctrl = $requestCtrl[0];
                $method = $requestCtrl[1] ?? '';
                $controllerName = "App\Controller\\" . ucfirst($ctrl) . 'Controller';
                $controllerObj = new $controllerName();
                if (method_exists($controllerName, $method)) {
                    $controllerObj->$method(...$matches);
                    $methodMatch = true;
                }
                break;
            }
        }
        if (!$pathMatch) {
            if (self::$pathNotFound) {
                call_user_func(self::$pathNotFound);

                return;
            } elseif (isset($config['error_ctrl'])) {
                $controllerName = "App\Controller\\" . $config['error_ctrl'];
                $file = $config['controller'] . '/' . $config['error_ctrl'] . '.php';
                if (file_exists($file)) {
                    if (method_exists($controllerName, 'pageNotFound')) {
                        (new $controllerName())->pageNotFound();
                        $methodMatch = true;

                        return;
                    }
                }
            }
            !headers_sent() and header('HTTP/1.1 404 Not Found');
            die('404 - The file not found');
        }
        if (!$methodMatch) {
            if (self::$methodNotAllowed) {
                call_user_func(self::$methodNotAllowed);

                return;
            } elseif (isset($config['error_ctrl'])) {
                $controllerName = "App\Controller\\" . $config['error_ctrl'];
                $file = $config['controller'] . '/' . $config['error_ctrl'] . '.php';
                if (file_exists($file)) {
                    if (method_exists($controllerName, 'invalidRequest')) {
                        (new $controllerName())->invalidRequest();

                        return;
                    }
                }
            }
            !headers_sent() and header('HTTP/1.1 400 Bad Request');
            die('404 - The method not allowed');
        }
    }

    /**
     * Calls when an error occured
     *
     * @param string|null $data Error data
     *
     * @return void
     */
    public static function error(?string $data = null)
    {
        global $config;
        if (self::$onError) {
            ob_start();
            call_user_func(self::$onError, $data);
            $content = ob_get_clean();
            echo $content;
            exit();
        } elseif (isset($config['error_ctrl'])) {
            $controllerName = "App\Controller\\" . $config['error_ctrl'];
            // $file = $config['controller'] . "/" . $config['error_ctrl'] . ".php";
            if (class_exists($controllerName)) {
                if (method_exists($controllerName, 'serverError')) {
                    ob_start();
                    (new $controllerName())->serverError($data);
                    $content = ob_get_clean();
                    echo $content;
                    exit();
                }
            }
        }
        !headers_sent() and header('HTTP/1.1 500 Internal Server Error');
        die('500 - Server Error');
    }

    /**
     * Performs Dispatch
     *
     * @param string $url URL
     *
     * @return void
     */
    public static function dispatch(string $url)
    {
        global $config;
        $url = ltrim($url, '/');
        $url = explode('/', $url);
        $controller = $url[0] . 'Controller';
        $method = $url[1];
        $controllerName = "App\Controller\\" . $config['error_ctrl'];
        if (class_exists($controllerName)) {
            $controller = "App\Controller\\" . ucfirst($controller);
            if (method_exists($controller, $method)) {
                (new $controller())->$method();
                exit();
            }
        }
        Router::error();
    }
}
