<?php

namespace Router;

class APIRoute extends Wrapper
{
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'patch';
    public const ACTION_VIEW = 'view';
    public const ACTION_LIST = 'list';
    public const ACTION_DELETE = 'delete';

    public function __construct(
        $rule,
        $api_class,
        $filters = [],
        $exclude = [],
        $name = ''
    ) {
        $this->init($rule, $api_class, $filters, $exclude, $name);
    }

    /**
     * Initialize the API route with the given parameters.
     *
     * @param string $rule      The route rule.
     * @param string $api_class The API class name.
     * @param mixed  $filters   Optional filters to apply to the route.
     * @param array  $exclude   Actions to exclude from the route.
     * @param string $name      Optional name for the route.
     */
    private function init(string $rule, string $api_class, $filters, array $exclude, string $name)
    {
        $actions = self::getRestAPIActions();

        $expression = basename(str_replace('\\', '/', $api_class));
        $id_placeholder = '';
        if (preg_match('/<([^>]+)>$/', $expression, $matches)) {
            $id_placeholder = $matches[1];
        }

        if (! empty($id_placeholder)) {
            $rule = str_replace('<' . $id_placeholder . '>', '', $rule);
        } else {
            $id_placeholder = '{(\d+):id}';
        }

        foreach ($actions as $action) {
            if (in_array($action, $exclude)) {
                continue;
            }
            $filters = is_array($filters) ? $filters : [];
            $this->addAction($action, $rule, $expression, $id_placeholder, $filters, $name);
        }
    }

    /**
     * Add an action to the API route.
     *
     * @param string $action         The action name.
     * @param string $rule           The route rule.
     * @param string $expression     The expression for the API class.
     * @param string $id_placeholder Placeholder for the ID in the route.
     * @param array  $filters        Optional filters to apply to the route.
     * @param string $name           Optional name for the route.
     */
    public function addAction($action, $rule, $expression, $id_placeholder, $filters = [], $name = '')
    {
        $ctrl = $expression . '/' . $action;
        $method = self::getReqMethodName($action);
        if (empty($name)) {
            $name = $expression;
        }
        $name = "$expression:$action";
        switch ($action) {
            case self::ACTION_LIST:
            case self::ACTION_CREATE:
                $this->routes[] = new Route($rule, $ctrl, $method, $filters, $name);

                break;
            case self::ACTION_VIEW:
            case self::ACTION_UPDATE:
            case self::ACTION_DELETE:
                $this->routes[] = new Route($rule . '/' . $id_placeholder, $ctrl, $method, $filters, $name);

                break;
            default:
                break;
        }
    }

    /**
     * Get the list of REST API actions.
     *
     * @return array
     */
    public static function getRestAPIActions()
    {
        return [
            self::ACTION_LIST,
            self::ACTION_VIEW,
            self::ACTION_CREATE,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
        ];
    }

    /**
     * Get the request method name for a given action.
     *
     * @param  string $action The action name.
     * @return string The request method name.
     */
    private static function getReqMethodName($action)
    {
        return self::actionReqMethodMap()[$action] ?? Router::METHOD_GET;
    }

    /**
     * Get the mapping of actions to request methods.
     *
     * @return array
     */
    public static function actionReqMethodMap()
    {
        return [
            self::ACTION_LIST => Router::METHOD_GET,
            self::ACTION_VIEW => Router::METHOD_GET,
            self::ACTION_CREATE => Router::METHOD_POST,
            self::ACTION_UPDATE => Router::METHOD_PUT,
            self::ACTION_DELETE => Router::METHOD_DELETE,
        ];
    }
}
