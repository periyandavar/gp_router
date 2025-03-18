<?php

namespace Router\Request;

class Request
{
    protected $post = [];
    protected $get = [];
    protected $headers = [];
    protected $url_param = [];
    protected $files = [];
    protected $server = [];
    protected $session = [];
    protected $cookies = [];

    /**
     * Request constructor.
     */
    public function __construct(array $url_params = [])
    {
        $this->init($url_params);
    }

    /**
     * Initialize Request
     */
    private function init(array $url_params = [])
    {
        $this->post = $_POST;
        $this->get = $_GET;
        $this->url_param = $url_params;
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->session = session_status() === PHP_SESSION_ACTIVE ? $_SESSION : [];
        $this->cookies = $_COOKIE;
    }

    /**
     * get the data
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function post(string $key = '', $default = null)
    {
        if (empty($key)) {
            return $this->post;
        }

        return $this->post[$key] ?? $default;
    }

    /**
     * get the query param.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key = '', $default = null)
    {
        if (empty($key)) {
            return $this->get;
        }

        return $this->get[$key] ?? $default;
    }

    /**
     * get the url param.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function urlParam(string $key = '', $default = null)
    {
        if (empty($key)) {
            return $this->url_param;
        }

        return $this->url_param[$key] ?? $default;
    }

    /**
     * get the header for the key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function header(string $key = '', $default = null)
    {
        if (empty($key)) {
            return $this->headers;
        }

        return $this->headers[$key] ?? $default;
    }

    /**
     * get the session for the key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function session(string $key, $default = null)
    {
        return $this->session[$key] ?? $default;
    }

    /**
     * get the cookie for the key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * get the server value for the key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function server(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }
}
