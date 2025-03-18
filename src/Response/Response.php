<?php

namespace Router\Response;

class Response
{
    protected $status = 200;
    protected $headers = [];
    protected $body;

    /**
     * Set the status code
     *
     * @param int $status
     *
     * @return Response
     */
    public function setStatusCode(int $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Get the Body
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set the header
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Response
     */
    public function setHeader(string $key, mixed $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Set the body
     *
     * @param string $body
     *
     * @return Response
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Send the response
     *
     * @return void
     */
    public function send()
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        if ($this->body) {
            echo $this->body;
        }
    }
}
