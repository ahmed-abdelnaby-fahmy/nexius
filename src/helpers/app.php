<?php

use Nexius\Config\Container;
use Nexius\Config\Dotenv;
use Nexius\Http\Exception\NotFoundHttpException;
use Nexius\Http\Exception\HttpException;
use Nexius\Http\Request;
use Nexius\Http\Response;


function env($name, $default = null)
{
    return $env = app()->get('env')->get($name, $default);
}

function response($body = null, $code = 200)
{
    return new Response($body, $code);
}

function ulid($length = 20)
{
    $bytes = random_bytes($length);
    $id = base64_encode($bytes);

    // Remove non-alphanumeric characters and trim to the desired length
    $id = preg_replace('/[^a-zA-Z0-9]/', '', $id);
    $id = substr($id, 0, $length);
    return $id;
}


function abort($code, $message = '', array $headers = [])
{
    if ($code == 404) {
        throw new NotFoundHttpException($message, null, 0, ["HTTP/1.0 404 Not Found"]);
    }
    throw new HttpException($code, $message, null, $headers);

}

function class_basename($class): string
{
    $class = is_object($class) ? get_class($class) : $class;

    return basename(str_replace('\\', '/', $class));
}


function app()
{
    return Container::getInstance();
}

function isJson($string)
{
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

function request()
{
    return app()->get(Request::class);
}

function dd()
{
    throw new Exception(json_encode(func_get_args()));
}