<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

    
    //www.example.com/api/stops
    Route::prefix('route')->group(function () 
    {
        //www.example.com/api/route
        Route::get("","BusLinesController@getRoute");
        
        //www.example.com/api/route/busStopsNearby
        Route::get("busStopsNearby","BusLinesController@getBusStopsNearby");
    });
    
    
    //www.example.com/api/lines
    Route::prefix('lines')->group(function () 
    {
        //www.example.com/api/lines/{7}
        Route::get("{id}",  "BusLinesController@getLineById");
            
        //www.example.com/api/lines?
        Route::get("",      "BusLinesController@getLines");
    });
    
    //www.example.com/api/stops
    Route::prefix('stops')->group(function () 
    {
        //www.example.com/api/lines/{7}
        Route::get("{id}",  "BusLinesController@getStopById");
            
        //www.example.com/api/lines?
        Route::get("",      "BusLinesController@getStops");
    });
        
        
        
//        
//        
//        //www.example.com/api/buseslines/id/
//        Route::prefix('id')->group(function () 
//        {
//            //www.example.com/api/buseslines/id/{7}
//            Route::get("{id}",               "BusLinesController@getLineById");
//        });
//        
//        //www.example.com/api/buseslines/{6}
//        Route::get("get",               "BusLinesController@getLinesByNumber");
//        //www.example.com/api/buseslines/{6}/{A}
//        Route::get("{number}/{letter}",      "BusesLineController@getLinesByNumberAndLetter");
//        //www.example.com/api/buseslines/{6}/{A}
//        Route::get("{number}/{letter}/{zone}",      "BusesLineController@getLinesByNumberAndLetterAndZone");
//        
//        //www.example.com/api/buseslines/
//        Route::get("",                       "BusLinesController@getLines");
//        
//        //www.example.com/api/buseslines/{6}
//        Route::get("stops",               "BusLinesController@getStops");
//    });