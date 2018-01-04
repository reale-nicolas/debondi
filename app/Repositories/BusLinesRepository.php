<?php

namespace App\Repositories;

use Illuminate\Container\Container;

/**
 * Description of BusesLineRepository
 *
 * @author nicore2000
 */
class BusLinesRepository extends BaseRepository
{
    
    public function __construct(Container $app) {
        parent::__construct($app);
    }
    
    
    public function getModel() 
    {
        return 'App\Models\BusLines';
    }
    

    
//    public function insert($number, $letter, $zone='', $interest_point = '', $neighborhoods='', $literal_path='')
//    {
//        $lineDB = $this->getModel();
//        $lineDB->number         = $number;
//        $lineDB->letter         = $letter;
//        $lineDB->zone           = $zone;
//        $lineDB->interest_points = $interest_point;
//        $lineDB->neighborhoods  = $neighborhoods;
//        $lineDB->literal_path   = $literal_path;
//
//        return $lineDB->save();
//    }
}
