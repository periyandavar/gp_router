<?php

namespace Router;

class APIRoute extends Wrapper
{
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
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

    private function init($rule, $api_class, $filters, $exclude, $name)
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

            $this->addAction($action, $rule, $expression, $id_placeholder, $filters, $name);
        }
    }

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

    private static function getReqMethodName($action)
    {
        return self::actionReqMethodMap()[$action] ?? Router::METHOD_GET;
    }

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
