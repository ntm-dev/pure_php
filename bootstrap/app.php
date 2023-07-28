<?php

require __DIR__ . "/functions.php";
require __DIR__ . "./../vendor/autoload.php";

$app = Core\Application::getInstance();
$app->dispatch();
