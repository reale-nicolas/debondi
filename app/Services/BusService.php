<?php
namespace App\Services;

use App\Repositories\BusLinesRepository;
use App\Repositories\BusStopsRepository;

/**
 * Description of BusService
 *
 * @author nicolas
 */
class BusService extends BaseService
{
    protected $busLineRepository;
    protected $busStopRepository;
    
    
    public function __construct(BusLinesRepository $busLine, BusStopsRepository $busStop) 
    {
        $this->busLineRepository = $busLine;
        $this->busStopRepository = $busStop;
    }
  
    
    public function getRoute($latFrom, $lngFrom, $latTo, $lngTo, $maxDistance = 800)
    {
        $stopsNearOrigin = $this->busStopRepository->getBusStopsNearby($latFrom, $lngFrom, $maxDistance);
    }
    
    

    public function getBusStopsNearby($latitude, $longitude, $radio=100)
    {
        return $this->busStopRepository->getBusStopsNearby($latitude, $longitude, $radio);   
    }
    
    
    public function  getLines($detailed = false)
    {
        $lines = $this->busLineRepository->all();
        
        if ($detailed) {
            foreach ($lines as $line) {
                $line->stops = $line->stops;
            }
        }
        
        return $lines;
    }
    
    
    public function getLinesBy($column, $value, $detailed = false)
    {
        $lines = $this->busLineRepository->findBy($column, $value);
        
        if ($detailed) {
            foreach ($lines as $line) {
                $line->stops = $line->stops;
            }
        }
        
        return $lines;
    }
    
    
    public function getLineById($id)
    {
        $line = $this->busLineRepository->find($id);
        
        if ($line) {
            $line->stops = $line->stops;
        }
        
        return $line;
    }
    
    
    public function  getStops($detailed = false)
    {
        $stops = $this->busStopRepository->all();
        
        if ($detailed) {
            foreach ($stops as $stop) {
                $stop->lines = $stop->lines;
            }
        }
        
        return $stops;
    }
    
    
    public function getStopsBy($column, $value, $detailed = false)
    {
        $stops = $this->busStopRepository->findBy($column, $value);
        
        if ($detailed) {
            foreach ($stops as $stop) {
                $stop->lines = $stop->lines;
            }
        }
        
        return $stops;
    }
    
    
    public function getStopById($id)
    {
        $stop = $this->busStopRepository->find($id);
        
        if ($stop) {
            $stop->lines = $stop->lines;
        }
        
        return $stop;
    }
}
