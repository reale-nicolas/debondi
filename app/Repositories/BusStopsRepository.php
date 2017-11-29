<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;

/**
 * Description of BusStopsRepository
 *
 * @author nicolas
 */
class BusStopsRepository extends BaseRepository
{
    public function __construct(Container $app) {
        parent::__construct($app);
    }

    //put your code here
    public function getModel() 
    {
        return 'App\Models\BusStops';
    }
    
    
       
    public function getBusStopsNearby($latitude, $longitude, $radio)
    {
        $query = "(DEGREES(
                    ACOS(
                          (
                            SIN(
                                RADIANS(".$latitude.")
                            ) * SIN(
                                RADIANS(latitude)
                            )
                          ) + 
                          (
                            COS(
                                RADIANS($latitude)
                            ) * COS(
                                RADIANS(latitude)
                            ) * COS(
                                RADIANS($longitude-longitude)
                            )
                          )
                        )
                    ) * 111.13384 * 1000)";
        
        $res = $this->model
            ->select(DB::raw('*, '.$query.' as distance'))
            ->whereRaw($query. " <= ".$radio)
            ->orderByRaw('distance ASC')
            ->get();
        
        return $res;
    }
    
    
//    public function insert($name, $latitude, $longitude, $enabled = true){}
//    public function insert1($data)
//    {
//        return $this->getModel()->create($data);
//        $paradaDB = $this->getModel();
//        $paradaDB->name   = $name;
//        $paradaDB->latitude  = $latitude;
//        $paradaDB->longitude = $longitude;
//        $paradaDB->enabled = $enabled;
//
//        return $paradaDB->save();
//    }

}
