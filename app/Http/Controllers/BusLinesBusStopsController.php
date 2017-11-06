<?php

namespace App\Http\Controllers;

use App\Interfaces\RepositoryInterface;

class BusLinesBusStopsController extends Controller
{
    private $busLinesBusStopsRepository;
    
    public function __construct(RepositoryInterface $busLinesBusStopsRepository) 
    {
        $this->busLinesBusStopsRepository = $busLinesBusStopsRepository;
    }
    
    public function create($data)
    {
        return $this->busLinesBusStopsRepository->create($data);
    }
}
