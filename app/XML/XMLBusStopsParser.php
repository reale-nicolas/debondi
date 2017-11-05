<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\XML;

//use stdClass;


use App\Models\Constants;
use TheSeer\Tokenizer\Exception;
/**
 * Description of XMLBusStopsParser
 *
 * @author nicolas
 */
class XMLBusStopsParser
{
    private $xmlObject;
    private $busStops       = Array();
    private $busRouteStops  = Array();
    
    
    public function __construct()
    {
        $this->initXmlFiles();
        
        $this->busStops         = $this->initBusStops();
        $this->busRouteStops    = $this->initBusRouteStops();
    }
    
    /**
     * Returns the different bus lines founded in the XML file as an Array
     * 
     * @return Array
     */
    public function getBusLines()
    {
        $bus = array();
        foreach($this->busRouteStops as $line => $arrRamal)
        {
            foreach ($arrRamal as $ramal => $stops)
            {
                $busLines = array();
                
                $busLines['line'] = $line;
                $busLines['ramal'] = $ramal;
                $busLines['zone'] = "";
                $bus[] = $busLines;
                
                foreach ($stops as $zone => $stop)
                {
                    if (!is_numeric($zone))
                    {
                        $busLines['line'] = $line;
                        $busLines['ramal'] = $ramal;
                        $busLines['zone'] = $zone;
                        $bus[] = $busLines;
                    }
                }
            }
        }
        
        return $bus;
    }
    
    
    public function getBusStops()
    {
        return $this->busStops;
    }
    
    
    /**
     * Returns an array with de line route specified by parameters.
     * 
     * @param String $line  Line number or letter to find.
     * @param String $ramal Ramal number or letter to find.
     * @param String $zone  Zone name to find.
     * 
     * @return Array
     */
    public function getBusRouteStops($line = '', $ramal = '', $zone = '')
    {
        if ($line == '' && $ramal == ''&& $zone == '') {
            return $this->busRouteStops;
        }
        elseif (is_numeric($line) && $ramal == ''&& $zone == '' ) {
            return (isset($this->busRouteStops[$line]) ? $this->busRouteStops[$line] : null);
        }
        elseif (is_numeric($line) && $ramal != ''&& $zone == '' ) {
            return (isset($this->busRouteStops[$line][$ramal]) ? $this->busRouteStops[$line][$ramal] : null);
        }
        elseif (is_numeric($line) && $ramal != ''&& $zone != '' ) {
            return (isset($this->busRouteStops[$line][$ramal][$zone]) ? $this->busRouteStops[$line][$ramal][$zone] : null);
        }
        
        return null;
    }
   
    
//    public function getBusLines()
//    {
//        foreach ($this->busRouteStops as $busLine)
//    }
    
    
    /**
     * Reads and initialize the XML file to parse.
     *
     *
     */
    protected function initXmlFiles()
    {
        $folderName = Constants::getValue("PATH_XML_RESOURCE_FOLDER");
        $arrFiles = array();
        
        $arrFilesDir = scandir($folderName);
        
        foreach ($arrFilesDir as $fileName) {
            if (is_file($folderName.$fileName)) {
                $arrFiles[] = $folderName.$fileName;
            }
        }
        
        if (count($arrFiles) > 1) { 
            throw new Exception("ERROR: Existe mas de un archivo XML de recorridos en ".$folderName);
            exit;
        }
        
        foreach ($arrFiles as $fileName) {
            $this->xmlObject = \simplexml_load_file($fileName, null, LIBXML_NOCDATA);
        }
    }
    
    /**
     * It returns an array that contains bus route for the $line specified
     * or for all lines founded if $line is not specified.
     * 
     * @param String $line Line name to get the route or null to get the route for all lines.
     * 
     * @return Array An array that contain all buses line defined into 
     * the folder PATH_BUS_ROUTE_FOLDER
     */
    protected function initBusRouteStops()
    {
        $arrAllBusStops = array();
        foreach($this->xmlObject->Document->Folder as $node) 
        {
            if(strpos (strtoupper($node->name), "RECORRIDO") !== false) 
            {
                $arrBusName = explode(" ", $node->name);
                if((count($arrBusName) != 2) || (!is_numeric($arrBusName[1] )))
                {
                    throw new Exception("ERROR: Nombre incorrecto del Recorrido en XML");
                    exit;
                }
                
                $busName = (String) $arrBusName[1];

                foreach($node->Placemark as $placemark) 
                {
                    $arrRamalName = explode("_", $placemark->name);
                    
                    if (count($arrRamalName) > 1) {
                        $ramalName = (String)$arrRamalName[0];
                        $zoneName = (String)$arrRamalName[1];
                    } else {
                        $ramalName = (String)$placemark->name;
                        $zoneName = "";
                    }
                    
                    $order = 0;
                    $arrStops = Array();
                    
                    $coordenadas = explode(",0",$placemark->LineString->coordinates);
                    
                    foreach($coordenadas as $pointCoord) 
                    {
                        $coordPoint = explode(",", trim($pointCoord));

                        if (isset($coordPoint[1])) 
                        {
                            $stop = Array();
                            $stop['latitude']    = (double)$coordPoint[1];
                            $stop['longitude']   = (double)$coordPoint[0];
                            $stop['order']      = $order;

                            $order = $order + 100;

                            $arrStops[] = $stop;
                        }
                    }
                    
                    if ($zoneName != '')
                    {
                        $arrAllBusStops[$busName][$ramalName][$zoneName] = $arrStops;
                    } else {
                        $arrAllBusStops[$busName][$ramalName] = $arrStops;
                    }
                }
            }
        }
        
        return $arrAllBusStops;
    }

    /**
     * It returns an array that contains all buses and its routes.
     * 
     * @return Array An array that contain all buses line defined into 
     * the folder PATH_BUS_ROUTE_FOLDER
     */
    protected function initBusStops()
    {
        $arrPoints = array();
        foreach($this->xmlObject->Document->Folder as $node) {
            
            if(strtoupper($node->name) == "PARADAS") {
                
                foreach($node->Placemark as $placemark) {
                    $stop = array();
                    $stop['name'] =  (String) $placemark->name;
                    
                    $coordenadas = explode(",0",$placemark->Point->coordinates);
                    foreach($coordenadas as $pointCoord) {
                        $coordPoint = explode(",", trim($pointCoord));
                        
                        if (isset($coordPoint[1])) {
                            $stop['latitude']    = trim($coordPoint[1]);
                            $stop['longitude']   = trim($coordPoint[0]);
                        }
                    }
                    $arrPoints[] = $stop;
                    
                }
            } 
        }
        return (count($arrPoints)>0 ? $arrPoints:null);
    }
    
     
    public function saveXML($arrElements)
    {
        foreach($this->xmlObject->Document->Folder as $node) {
            
            if(strpos (strtoupper($node->name), "RECORRIDO") !== false) {
                foreach($node->Placemark as $placemark) {
                    
                    
                    
                    
                    foreach($arrElements as $k=>$value)
                    {
                        if ( $k != 'PARADAS')
                        {
                            if (strtoupper($value->name) == strtoupper($placemark->name))
                            {
                                echo "<br><br><br>".$placemark->name."<br>";
                                $line = '';
                                foreach($value->paradas  as $c=>$v)
                                {
//                                    echo "<br>".number_format($v->longitud,7).",".number_format($v->latitud,7).",0";
                                    $line .= "\n\t".($v->longitud).",".($v->latitud).",0";
                                }
                                echo $line;
                                $placemark->LineString->coordinates = $line;
                            }
                        }
                    }
                }
            }
        }
        $this->xmlObject->asXML("pruba.xml");
                
//                    foreach($placemark->LineString->coordinates as $coordinates)
//                    {
//                        $coordenadas = explode(",0",$coordinates);
//                        foreach($coordenadas as $pointCoord) {
//                            $coordPoint = explode(",", trim($pointCoord));
//                            
//                            if (isset($coordPoint[1])) {
//                                $parada = new \stdClass();
//                                $parada->latitud = (double)$coordPoint[1];
//                                $parada->longitud= (double)$coordPoint[0];
//                                
//                                $point->paradas[] = $parada;
//                            }
//                        }
//                    }
//                    
//                    $arrPoints[$point->name] = $point;
//                }
//                
//            }
    }
}
