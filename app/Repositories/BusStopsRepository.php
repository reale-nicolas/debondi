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
class BusStopsRepository extends BaseRepository
{
    public function __construct(\Illuminate\Container\Container $app) {
        parent::__construct($app);
    }

    //put your code here
    public function getModel() 
    {
        return 'App\Models\BusStops';
    }
    
    /**
     * 
     * @param float $latitud
     * @param float $longitud
     * @return type
     */
    public function getBusStopsByLatLng($latitud, $longitud)
    {
        $res = $this->model
            ->where("latitude", $latitud)
            ->where("longitude", $longitud)
            ->get()->first();
        
        if(count($res)>0)
            return $res;
        
        return false;
    }
    
    
    
//    public function errors() {
//        ;
//    }    
//    
//    public function all(array $related = null) {
//        return $this->getModel()->all();
//    }
//    
//    public function get($id, array $related = null) {
//        ;
//    }
//    
//    public function getWhere($column, $value, array $related = null) {
//        ;
//    }
//    
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
