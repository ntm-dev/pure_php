<?php

require "./../vendor/autoload.php";
require "functions.php";

$configs = require "./../config/app.php";
foreach ($configs['aliases'] as $alias => $class) {
    class_alias($class, $alias);
}
unset($configs);

require "./../routes/web.php";

Route::dispatch();
