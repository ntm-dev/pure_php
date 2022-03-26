<?php

Route::get('/', function() {
    echo "first route";
});
Route::get('/first-model', "HomeController@index");
