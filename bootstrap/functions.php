<?php

use Core\Application;
use Core\Support\Helper\Str;
use Core\Support\Helper\Arr;
use Core\Support\Facades\View;
use Core\Support\Helper\Collection;
use Core\Http\Exception\NotFoundException;

function dd()
{
    array_map(function ($x) {
        dump($x);
    }, func_get_args());
    die;
}

function app()
{
    return Application::getInstance();
}

function base_path($path = '')
{
    return Str::beforeLast(__DIR__, DIRECTORY_SEPARATOR) . ($path ? (DIRECTORY_SEPARATOR.$path) : '');
}

function core_path($path = '')
{
    return base_path("core" . ($path ? DIRECTORY_SEPARATOR.$path : ''));
}

function app_path($path = '')
{
    return base_path("app" . ($path ? DIRECTORY_SEPARATOR.$path : ''));
}

function public_path($path = '')
{
    return base_path("public" . ($path ? DIRECTORY_SEPARATOR.$path : ''));
}

function storage_path($path = '')
{
    return base_path("storage" . ($path ? DIRECTORY_SEPARATOR.$path : ''));
}

function collect($data)
{
    return new Collection($data);
}

function view($template, $data = [])
{
    return View::render($template, $data);
}

function config($key, $default = null)
{
    return Arr::get(app()->getConfig(), $key, $default);
}

function abort(int $statusCode)
{
    throw new NotFoundException('Not Found');
}

function app_name()
{
    return config('APP_NAME', "PURE PHP");
}

/**
 * Get last fatal error.
 *
 * @return  array
 */
function get_last_fatal_error()
{
    $error = error_get_last();

    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        return $error;
    }

    return [];
}

/**
 * Get container instance or make a service with container.
 *
 * @return \Core\Container\Container|mixed;
 */
function container($service = '', $share = false)
{
    $container = Core\Container\Container::getInstance();

    if ($service) {
        if ($share && !$container->bound($service)) {
            $container->singleton($service);
        }

        return $container->make($service);
    }

    return $container;
}