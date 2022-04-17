<?php

require "functions.php";
require "./../vendor/autoload.php";

$app = Core\Application::getInstance();
$app->dispatch();
