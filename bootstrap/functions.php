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
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $view = new Core\Views\Smarty\Base($template);
    $view->assign($data);

    return $view->display();
}

function config($key, $default = null)
{
    $configs = Core\Application::getInstance()->getConfig();

    return $configs[$key] ?? $default;
}
