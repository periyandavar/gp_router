<?php

namespace Router;

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
    protected Request $request;
    protected Response $response;
    protected array $url_params = [];
    private string $regex;
    private array $url_keys = [];

    private string $controller;
    private string $action;

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
    public function setPath(string $path): self
    {
        $this->path = $path;
        $this->setRegex();
        $this->setUrlKeys();

        return $this;
    }

    /**
     * Get the value of expression
     *
     * @return mixed
     */
    public function getExpression(): mixed
    {
        return $this->expression;
    }

    /**
     * Set Controller Action
     *
     * @return void
     */
    private function setControllerAction()
    {
        if (is_string($this->expression)) {
            $parts = explode('/', $this->expression);
            if (count($parts) < 2) {
                $this->controller = $this->expression;
                $this->action = 'invoke';

                return;
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
        $this->setControllerAction();

        return $this;
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
        return $this->url_params;
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
        $this->url_params = $url_params;

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
            $this->url_params[$this->url_keys[$key]] = $value;
        }
    }

    /**
     * set the regex
     *
     * @return void
     */
    private function setRegex()
    {
        if (empty($this->path)) {
            return;
        }

        $this->regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*):([^\}]+)\}/',
            function($matches) {
                return $matches[2];
            },
            $this->path
        );
    }

    /**
     * set the url keys
     *
     * @return void
     */
    private function setUrlKeys()
    {
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\:/', $this->path, $paramMatches);
        $this->url_keys = $paramMatches[1];
    }

    /**
     * Get the value of url_keys
     *
     * @return array
     */
    public function getUrlKeys()
    {
        return $this->url_keys;
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
        return $this->controller;
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
}
