<?php

namespace Router\Request;

class Request
{
    protected $post = [];
    protected $get = [];
    protected $data = [];
    protected $headers = [];
    protected $url_param = [];
    protected $files = [];
    protected $server = [];
    protected $session = [];
    protected $cookies = [];

    protected $escape = true;

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
        $this->data = (array) json_decode(file_get_contents('php://input'), true);
        $this->url_param = $url_params;
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->session = session_status() === PHP_SESSION_ACTIVE ? $_SESSION : [];
        $this->cookies = $_COOKIE;
    }

    /**
     * Get data from the request.
     *
     * @param string $key
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getData($key, $name = '', $default = null)
    {
        if (! property_exists($this, $key)) {
            return $default;
        }

        if (empty($name)) {
            return $this->escape ? $this->escape($this->$key) : $this->$key;
        }

        $value = $this->$key[$name] ?? $default;

        return ($this->escape && is_string($value)) ? htmlspecialchars($value) : $value;
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
        return $this->getData('post', $key, $default);
    }

    /**
     * get the data
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function data(string $key = '', $default = null)
    {
        return $this->getData('data', $key, $default);
    }

    /**
     * escape the input data.
     *
     * @param array $input
     *
     * @return array
     */
    private function escape(array $input)
    {
        $data = $input;
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->escape($value);
            } else {
                $data[$key] = htmlspecialchars($value);
            }
        }

        return $data;
    }

    /**
     * Set whether to escape the input data.
     *
     * @param bool $escape
     */
    public function setEscape(bool $escape)
    {
        $this->escape = $escape;
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
        return $this->getData('get', $key, $default);
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

    /**
     * Check if a header exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasHeader(string $key): bool
    {
        return isset($this->headers[$key]);
    }

    /**
     * Set a session variable.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function setSession(string $key, $value): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[$key] = $value;
        }

        $this->session = $_SESSION;
    }
}
