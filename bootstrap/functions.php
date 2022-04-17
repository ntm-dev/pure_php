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

function view($template, $data = [])
{
    $view = new Core\Views\Base($template);
    $view->assign($data);

    return $view->display();
}

function config($key, $default = null)
{
    $configs = Core\Application::getInstance()->getConfig();

    return $configs[$key] ?? $default;
}

function app_name()
{
    return config('APP_NAME', "PURE PHP");
}
