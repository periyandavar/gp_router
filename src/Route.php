<?php

namespace Router;

use Exception;
use Router\Filter\Filter;
use Router\Request\Request;
use Router\Response\Response;

class Route
{
    protected string $name;
    protected string $path;
    protected mixed $expression;
    protected array $filters = [];
    protected string $method;
    protected string $prefix = '';
    protected Request $request;
    protected Response $response;
    protected array $urlParams = [];
    private string $regex;
    private array $urlKeys = [];
    private string $override_ctrl;

    private string $controller;
    private string $action;

    public function __construct($rule, $expression, string $method = Router::METHOD_GET, array $filter = [], string $name = '')
    {
        $this->setPath($rule)
            ->setExpression($expression)
            ->setMethod($method)
            ->setFilters($filter)
            ->setName($name);
    }

    /**
     * Get the value of rule
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the value of rule
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath(string $path): Route
    {
        $this->path = $path;
        if (empty($this->path)) {
            return $this;
        }

        $result = preg_match_all('/\{(\([^)]+\))\:(\w+)\}/', $path, $paramMatches);

        if ($result) {
            $path = str_replace($paramMatches[0], '', $path) . implode('/', $paramMatches[1]);
            $this->urlKeys = $paramMatches[2];
        }
        $this->regex = $path;

        return $this;
    }

    /**
     * Get the value of expression
     *
     * @return mixed
     */
    public function getExpression(): mixed
    {
        if (!empty($this->prefix)) {
            return "{$this->prefix}/$this->expression";
        }

        return $this->expression;
    }

    /**
     * Set the value of expression
     *
     * @param mixed $expression
     *
     * @return self
     */
    public function setExpression(mixed $expression): self
    {
        $this->expression = $expression;

        if (is_string($this->expression)) {
            $parts = explode('/', ltrim($this->expression, '/'));
            if (count($parts) < 2) {
                $this->controller = $this->expression;
                $this->action = 'invoke';

                return $this;
            }
            $this->controller = reset($parts);
            array_shift($parts);
            $this->action = reset($parts);
        }
        if (is_array($this->expression)) {
            $this->controller = $this->expression[0] ?? '';
            $this->action = $this->expression[1] ?? '';
        }
        if (is_callable($this->expression)) {
            $this->controller = $this->expression;
        }

        return $this;
    }

    public function setController(string $ctrl)
    {
        // throw new Exception();
        $this->override_ctrl = $ctrl;
    }

    /**
     * Get the value of filters
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Set the value of filters
     *
     * @param array $filters
     *
     * @return self
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get the value of method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the value of method
     *
     * @param string $method
     *
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the value of request
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Set the value of request
     *
     * @param Request $request
     *
     * @return self
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the value of response
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Set the value of response
     *
     * @param Response $response
     *
     * @return self
     */
    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Add a filter to the route
     *
     * @param mixed $filter
     *
     * @return void
     */
    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of url_params
     *
     * @return array
     */
    public function getUrlParams(): array
    {
        return $this->urlParams;
    }

    /**
     * Set the value of url_params
     *
     * @param array $url_params
     *
     * @return self
     */
    public function setUrlParams(array $url_params): self
    {
        $this->urlParams = $url_params;

        return $this;
    }

    /**
     * Set the Matches.
     *
     * @param array $matches
     *
     * @return void
     */
    public function setMatches(array $matches = [])
    {
        foreach ($matches as $key => $value) {
            $this->urlParams[$this->urlKeys[$key]] = $value;
        }
    }

    /**
     * Get the value of regex
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Handle the filters
     *
     * @return void
     */
    public function handleFilters()
    {
        foreach ($this->filters as $filter) {
            if (is_callable($filter)) {
                $filter($this->request, $this->response);
                continue;
            }
            if (is_array($filter) && !empty($filter[0]) && !empty($filter[1])) {
                $filter = new $filter[0]();
                call_user_func([$filter, $filter[1]], $this->request, $this->response);
            }
            if (is_string($filter)) {
                $filter = new $filter();
            }

            if ($filter instanceof Filter) {
                $filter->filter($this->request, $this->response);
                continue;
            }
        }
    }

    /**
     * Get the value of controller
     *
     * @return mixed
     */
    public function getController()
    {
        if (! isset($this->override_ctrl)) {
            $ctrlName = ucfirst($this->controller);
            if (! empty($this->prefix)) {
                $ctrlName = $this->prefix . '\\' . $ctrlName;
            }

            $this->override_ctrl = $ctrlName;
        }

        return $this->override_ctrl;
    }

    /**
     * Get the value of action
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }
}
