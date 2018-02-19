<?php

namespace App\Providers;

use App\Repositories\BusLinesBusStopsRepository;
use App\Repositories\BusLinesRepository;
use App\Repositories\BusStopsRepository;
use App\XML\XMLBusStopsParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {   
        $this->app->singleton('App\XML\XMLBusStopsParser', function()
        {
            return new XMLBusStopsParser();
        });
        
        
//        $this->app
//            ->when('App\Http\Controllers\BusStopsController')
//            ->needs('App\Interfaces\RepositoryInterface')
//            ->give(function () {
//                return new BusStopsRepository($this->app);
//        });
//        
//        $this->app
//            ->when('App\Http\Controllers\BusLinesController')
//            ->needs('App\Interfaces\RepositoryInterface')
//            ->give(function () {
//                return new BusLinesRepository($this->app);
//        });
//        
//        $this->app
//            ->when('App\Http\Controllers\BusLinesBusStopsController')
//            ->needs('App\Interfaces\RepositoryInterface')
//            ->give(function () {
//                return new BusLinesBusStopsRepository($this->app);
//        });
        
    }
}
