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

    return $view->render();
}

function config($key, $default = null)
{
    return Core\Support\Helper\Arr::get(Core\Application::getInstance()->getConfig(), $key, $default);
}

function abort(int $statusCode)
{
    throw new Core\Http\Request\Exception\NotFoundException('Not Found');
}

function app_name()
{
    return config('APP_NAME', "PURE PHP");
}
