<?php

require "./../vendor/autoload.php";
require "functions.php";

$app = Core\Application::getInstance();
$app->dispatch();
