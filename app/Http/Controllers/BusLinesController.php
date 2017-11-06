<?php

namespace App\Http\Controllers;

use App\Repositories\BusLinesRepository;

class BusLinesController extends Controller
{
    private $busLinesRepository;
    
    public function __construct(BusLinesRepository $busLinesRepository) 
    {
        $this->busLinesRepository = $busLinesRepository;
    }
    
    public function create($data)
    {
        return $this->busLinesRepository->create($data);
    }
    
    
    public function getAll()
    {
        $line = $this->busLinesRepository->route();
        echo $line->number;
//        $r = $line->route;
//        echo "<br>".$r->order;
//        var_dump($line);
        foreach ($line as $l) {
            echo $l->id;
//            var_dump($l);
        }
        
    }
    
    public function getAllLines()
    {
        return $this->busLinesRepository->all();
    }
}
