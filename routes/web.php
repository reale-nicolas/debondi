<?php

use App\XML\XMLBusStopsParser;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get("/seeder", "BusLinesController@getAll");

Route::get('/', function () {
    
                
    $busStops = App::make("App\Http\Controllers\BusLinesController");
//    App::make("App\Repositories\BusLStopsRepository");
    $a = $busStops->getAllLines();
    
    echo "<pre>";
    print_r($a);
    echo "</pre>";
});
