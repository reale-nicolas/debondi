<?php

namespace App\Repositories;

use App\Models\BusLines;

/**
 * Description of BusesLineRepository
 *
 * @author nicore2000
 */
class BusLinesRepository extends BaseRepository
{
    
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
