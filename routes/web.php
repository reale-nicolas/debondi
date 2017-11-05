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

Route::get('/', function () {
    $a =  new XMLBusStopsParser();
//    $lines = $a->getBusLines();
//    echo "<pre>";
//    print_r($lines);
//    echo "</pre>";
    
    $b = $a->getBusStops();
    echo "<pre>";
    print_r($b);
    echo "</pre>";
//    return view('welcome');
});
