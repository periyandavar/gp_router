<?php

namespace Router\Response;

use InvalidArgumentException;
use System\Core\Utility;

class Response
{
    public const TYPE_JSON = 'json';
    public const TYPE_HTML = 'html';
    public const TYPE_XML = 'xml';
    public const TYPE_TEXT = 'text';
    public const TYPE_CSV = 'csv';
    public const TYPE_YAML = 'yaml';
    public const TYPE_BINARY = 'binary';
    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';
    public const TYPE_STREAM = 'stream';

    protected $status = 200;
    protected $headers = [];

    protected $content;
    protected $body;

    protected $type;

    public function __construct($status = 200, $headers = [], $body = '', $type = null)
    {
        $this->setStatusCode($status)
            ->setHeaders($headers)
            ->setBody($body)
            ->setType($type);
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

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

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set the body
     *
     * @param mixed $body
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
        if (headers_sent()) {
            return;
        }
        http_response_code($this->status);

        $this->generate();
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        echo $this->content;
    }

    public function generate()
    {
        $type = $this->getType() ?? self::TYPE_TEXT;
        $data = $this->getBody();
        switch (strtolower($type)) {
            case self::TYPE_JSON:
                $this->setHeader('Content-Type', 'application/json');
                $this->setContent(json_encode($data));

                break;

            case self::TYPE_HTML:
                $this->setHeader('Content-Type', 'text/html');
                $this->setContent($data); // Assuming $data is already HTML

                break;

            case self::TYPE_XML:
                $this->setHeader('Content-Type', 'application/xml');
                $this->setContent(Utility::arrayToXml($data));

                break;

            case self::TYPE_TEXT:
                $this->setHeader('Content-Type', 'text/plain');
                $this->setContent($data);

                break;

            case self::TYPE_CSV:
                $this->setHeader('Content-Type', 'text/csv');
                $this->setContent(Utility::arrayToCsv($data));

                break;

            case self::TYPE_YAML:
                $this->setHeader('Content-Type', 'application/x-yaml');
                $this->setContent(Utility::arrayToYaml($data));

                break;

            case self::TYPE_BINARY:
                $this->setHeader('Content-Type', 'application/octet-stream');
                $this->setContent($data); // Assuming $data is binary content

                break;

            case self::TYPE_IMAGE:
                // Assuming $data is the path to the image file
                $this->setHeader('Content-Type', 'image/jpeg'); // Change as needed
                $this->setContent(file_get_contents($data));

                break;

            case self::TYPE_AUDIO:
                // Assuming $data is the path to the audio file
                $this->setHeader('Content-Type', 'audio/mpeg'); // Change as needed
                $this->setContent(file_get_contents($data));

                break;

            case self::TYPE_VIDEO:
                // Assuming $data is the path to the video file
                $this->setHeader('Content-Type', 'video/mp4'); // Change as needed
                $this->setContent(file_get_contents($data));

                break;

            case self::TYPE_STREAM:
                $this->setHeader('Content-Type', 'application/octet-stream'); // Default for streaming
                $this->setBody($data); // Assuming $data is a callable or resource for streaming

                break;

            default:
                throw new InvalidArgumentException("Unsupported response type: $type");
        }
    }

    private function setContent($content)
    {
        $this->content = $content;
    }
}
