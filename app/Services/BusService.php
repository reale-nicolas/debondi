<?php
namespace App\Services;

use App\Repositories\BusLinesRepository;
use App\Repositories\BusStopsRepository;
use App\Repositories\GeneralRepository;

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
//        parent::__construct($generalRepository);
        $this->busLineRepository = $busLine;
        $this->busStopRepository = $busStop;
    }
  
    
    public function getRoute($latFrom, $lngFrom, $latTo, $lngTo, $maxDistance = 800)
    {
        try {
            $result = [];
            $optionRoute = $this->busLineRepository->getRouteOptions($latFrom, $lngFrom, $latTo, $lngTo, $maxDistance);

            if ($optionRoute && count($optionRoute) > 0)
            {
                foreach($optionRoute as $option)
                {
                    if ($option->id_lines_origin == $option->id_lines_destiny) 
                    {
//                        $line = $this->busLineRepository->find($option->id_line_origin);
                        $result[] = array(
                            "distance"  => $option->distance_origin+$option->distance_destiny,
                            "stop_from" => $option->id_stop_origin,
                            "stop_to"   => $option->id_stop_destiny,
                            "route"     => [
                                [
                                    'id'    => $option->id_lines_origin,
                                    'line'  => $option->line,
                                    'ramal' => $option->ramal,
                                    'zone'  => $option->txt_zones
                                ]
                            ]
                        );
                    } else {
                        $lineOrigin  = $this->busLineRepository->find($option->id_line_origin);
                        $lineDestiny = $this->busLineRepository->find($option->id_line_destiny);
                        
                        $result[] = array(
                            "distance"  => $option->distance,
                            "stop_from" => $option->id_stop_origin,
                            "stop_to"   => $option->id_stop_destiny,
                            "route"     => [
                                [
                                    'id'    => $lineOrigin->id,
                                    'line'  => $lineOrigin->line,
                                    'ramal' => $lineOrigin->ramal
                                ],
                                [
                                    'id'    => $lineDestiny->id,
                                    'line'  => $lineDestiny->line,
                                    'ramal' => $lineDestiny->ramal
                                ]
                            ]
                        );
                    }
                }
            }
            
            return $result;
            
        } catch (Exception $e)
        {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException;
        }
    }

//    public function getRoute($latFrom, $lngFrom, $latTo, $lngTo, $maxDistance = 800)
//    {
//        $distance = distanceBetweenTerrainPoints($latFrom, $lngFrom, $latTo, $lngTo, "m");
//        if ($distance <= $maxDistance)
//        {
//            echo "Vaya caminando señor!!! Solo son: ".$distance;
//            exit;
//        }
//        
//        $stopsNearOrigin = $this->busStopRepository->getBusStopsNearby($latFrom, $lngFrom, $maxDistance);
//  
//        foreach ($stopsNearOrigin as $stop)
//        {
//            $stop->stopsOptions = $this->busStopRepository->getBusStopsNearby($latTo, $lngTo, $maxDistance-$stop->distance);
//            $stop->lines = $stop->lines;
//            
//            foreach ($stop->stopsOptions as $stopDestination)
//            {
//                $stopDestination->lines = $stopDestination->lines;
//            }
//        }
//        
//        return ($stopsNearOrigin);
//    }

//debondi.app/api/route/?latFrom=-24.8092086&lngFrom=-65.38786479999999&latTo=-24.788584&lngTo=-65.4122110&maxDistance=1000    

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
