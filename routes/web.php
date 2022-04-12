<?php

use Core\Routing\Route;

Route::get('/', function() {
    echo "first route";
});
Route::get('/first-model', "HomeController@index");
