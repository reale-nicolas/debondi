<?php

namespace App\Http\Controllers;

use App\Interfaces\RepositoryInterface;

class BusLinesController extends Controller
{
    private $busLines;
    
    public function __construct(RepositoryInterface $busLine) 
    {
        $this->busLines = $busLine;
    }
    
    public function create($data)
    {
        return $this->busLines->create($data);
    }
}
