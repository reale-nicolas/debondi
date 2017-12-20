<?php

namespace App\AStar;

use App\Repositories\BusLinesRepository;
use App\Repositories\BusStopsRepository;

/**
 * Description of AStarServices
 *
 * @author nicolas
 */
class AStarServices 
{
    protected $linesDB;
    protected $stopsDB;
    
    public function __construct(BusLinesRepository $linesDB, BusStopsRepository $stopsDB) 
    {
        $this->linesDB = $linesDB;
        $this->stopsDB = $stopsDB;
    }
    
    
    public function getBusStopsNearby()
    {
        return $this->stopsDB->getBusStopsNearby('-24.8092086','-65.38786479', '800');
    }
    
}
