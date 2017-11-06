<?php

namespace App\Http\Controllers;

use App\Repositories\BusStopsRepository;


class BusStopsController extends Controller
{
    private $busStopsRepository;
    
    public function __construct(BusStopsRepository $busStopsRepository) 
    {
        $this->busStopsRepository = $busStopsRepository;
    }
    
    public function create($data)
    {
        return $this->busStopsRepository->create($data);
    }
}
