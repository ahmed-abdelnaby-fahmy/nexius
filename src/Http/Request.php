<?php

namespace Nexius\Http;

use Nexius\Http\Interfaces\RequestInterface;
use Nexius\Validation\Validator;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request implements RequestInterface
{
    protected $validatedData;
    public $request;
    protected $data;
    protected $json;
    protected $files;
    protected $server;
    protected $cookies;
    protected $headers;

    public function __construct($data = null)
    {
        $this->setup($data);
    }

    protected function setup($data = null)
    {
        $this->request = SymfonyRequest::createFromGlobals();
        $this->data = $this->request->query->all() + $this->request->request->all();
        $this->files = $this->request->files;
        $this->server = $this->request->server;
        $this->cookies = $this->request->cookies;
        $this->headers = $this->request->headers;
        $this->json = json_decode($this->request->getContent(), true);

        if (!empty($data)) {
            $this->validate($data);
        }
    }

    public function getMethod()
    {
        return $this->request->getMethod();
    }

    protected function validate($data)
    {
        $validator = Validator::make($data, $this->rules());
        $validated = $validator->getValidData();
        $this->validatedData = $validated;
    }

    public function validated()
    {
        return $this->validatedData;
    }

    public function input($key, $default = null)
    {
        return $this->data[$key] ?? $this->json[$key] ?? $default;
    }

    public function has($key)
    {
        return isset($this->data[$key]) || isset($this->json[$key]);
    }

    public function all()
    {
        return $this->data + (is_array($this->json) ? $this->json : []);
    }

    public function only()
    {
        $argv = func_get_args();
        return array_intersect_key($this->all(), array_flip($argv));
    }

    public function headers()
    {
        return $this->headers;
    }

    public function rules(): array
    {
        return [];
    }

    public function file($key)
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile($key)
    {
        return isset($this->files[$key]);
    }

    public function __get(string $name)
    {
        return $this->input($name, null);
    }

    public function __set(string $name, string $value)
    {
        return $this->data[$name] = $value;
    }

    public function getPathInfo(): string
    {
        // Assuming REQUEST_URI and SCRIPT_NAME are available and correct.
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        // Remove query string from REQUEST_URI if present.
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        // If the request URI starts with the script name, or if the script name
        // is located at the root and the request URI starts with it,
        // then this is the path info.
        if (strpos($requestUri, $scriptName) === 0) {
            $pathInfo = substr($requestUri, strlen($scriptName));
        } else {
            $pathInfo = $requestUri;
        }

        return '/' . ltrim($pathInfo, '/');
    }
}
