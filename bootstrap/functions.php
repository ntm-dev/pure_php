<?php

function dd()
{
    array_map(function ($x) {
        dump($x);
    }, func_get_args());
    die;
}

function root_path()
{
    return $_SERVER['PWD'];
}

function app_path()
{
    return root_path() . "/app";
}

function public_path()
{
    return root_path() . "/public";
}

function collect($data)
{
    return new Core\Support\Helper\Collection($data);
}

function config($key, $default = null)
{
    $dotenv = Dotenv\Dotenv::createImmutable(root_path());
    $dotenv->safeLoad();

    return $_ENV[$key] ?? $default;
}
