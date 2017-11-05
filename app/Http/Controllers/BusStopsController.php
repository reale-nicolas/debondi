<?php

namespace App\Http\Controllers;

use App\Interfaces\RepositoryInterface;


class BusStopsController extends Controller
{
    private $busStops;
    
    public function __construct(RepositoryInterface $busStops) 
    {
        $this->busStops = $busStops;
    }
    
    public function create($data)
    {
        return $this->busStops->create($data);
    }
}
