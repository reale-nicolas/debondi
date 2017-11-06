<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories;

/**
 * Description of BusStopsRepository
 *
 * @author nicolas
 */
class BusLinesBusStopsRepository extends BaseRepository
{
    public function __construct(\Illuminate\Container\Container $app) {
        parent::__construct($app);
    }

    //put your code here
    public function getModel() 
    {
        return 'App\Models\BusLinesBusStops';
    }
    
}
