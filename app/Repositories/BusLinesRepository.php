<?php

namespace App\Repositories;

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
//    
//    public function busesLineRoute($busLines) {
////        return $busLines->hasMany(BusesLineRoute::class, 'line_id')->get();
//    }
//    
//    public function busesLineStop($busLines) {
////        return $busLines->hasMany(BusesLineStop::class, 'line_id')->get();
//    }
//    
//    public function getLines()
//    {
//        return $this->getModel()->all();
//    }
//    
//    
//    public function getLinesByNumber($number)
//    {
//        return $this->getModel()->where("number", $number)
//                                ->get();
//    }
//    
//    
//    public function getLinesByNumberAndLetter($number, $letter)
//    {
//        return $this->getModel()->where("number", $number)
//                                ->where("letter", $letter)
//                                ->get();
//    }
//    
//    
//    public function getLinesById($id)
//    {
//        return $this->getModel()->find($id);
//    }
//    
//    
//    
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
