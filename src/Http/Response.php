<?php

namespace Nexius\Http;
class Response
{
    private $headers = [];
    private $httpResponseCode;
    private $body;

    private static $httpCodes = [
        200 => "OK",
        304 => "Not Modified",
        403 => "Forbidden",
        404 => "Not Found",
        422 => "Unprocessable Entity"
    ];

    public function __construct($body = null, $code = 200)
    {
        $this->setBody($body);
        $this->httpResponseCode = $code == 0 ? 200 : $code;
    }

    public function send()
    {
        if (array_key_exists($this->httpResponseCode, self::$httpCodes))
            header("HTTP/1.1 {$this->httpResponseCode} " . self::$httpCodes[$this->httpResponseCode]);
        foreach ($this->headers as $key => $header) {
            if (is_string($key))
                header($key . ':' . $header);
            else
                header($header);
        }

        die($this->body);
    }

    /**
     * Gets the value of headers.
     *
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Sets the value of headers.
     *
     * @param mixed $headers the headers
     *
     * @return self
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set the value of body.
     **
     * @return self
     */
    public function setBody($body)
    {

        $this->body = is_array($body) || is_object($body) ? json_encode($body) : $body;
        return $this;
    }

    /**
     * Gets the value of httpResponseCode.
     *
     * @return mixed
     */
    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }

    /**
     * Sets the value of httpResponseCode.
     *
     * @param mixed $httpResponseCode the http response code
     *
     * @return self
     */
    public function setHttpResponseCode($httpResponseCode)
    {
        $this->httpResponseCode = $httpResponseCode;

        return $this;
    }

    /**
     * Gets the value of body.
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    public function created($content, $headers = [])
    {
        return $this->json([
            'status' => 'success',
            'data' => $content,
        ], $headers);
    }
    public function update($content, $headers = [])
    {
        return $this->json([
            'status' => 'success',
            'data' => $content,
        ], $headers);
    }

    public function error($content, $headers = [])
    {
        $content = $content instanceof \Exception ? [$content->getMessage(), $content->getTrace()] : $content;
        return $this->json([
            'status' => 'error',
            'data' => $content,
        ], $headers);
    }

    public function json($content, $headers = [])
    {
        $headers = array_merge([
            'content-type' => 'application/json',
            'Accept' => 'application/json',
        ], $headers);
        $this->httpResponseCode = 200;
        $this->setHeaders($headers);
        $this->setBody($content);
        return $this;
    }
}