<?php

use Core\Routing\Route;

// Route::get('/', function() {
//     echo "first route";
// });
Route::group('/line', function() {
    Route::get('/', "Line\\MessageController@index");
});
Route::get('/', "HomeController@index");
Route::get('/first/model', "HomeController@index");
