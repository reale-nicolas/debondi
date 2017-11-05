<?php

namespace App\XML;

use const PATH_BUS_ROUTE_FOLDER;

define("PATH_BUS_ROUTE_FOLDER", __DIR__."/../../resources/xml/bus_routes/");

/**
 * This class implements all methods to read a specific directory and
 * build an Array with all buses lines and its respective route. Each file
 * into the directory represents a line bus and its path.
 *
 * @author Reale, Nicolás
 */
class xmlBusRouteFileParser
{
    private $xmlObject;
    private $busRoute = null;
   
    
    /**
     * It returns an array that contains all buses and its routes.
     * 
     * @return Array An array that contain all buses line defined into 
     * the folder PATH_BUS_ROUTE_FOLDER
     */
    public function getBusRoute()
    {
        if ($this->busRoute == null){
            $this->busRoute = $this->setUpBusRoute();
        } 
        
        return $this->busRoute;
    }
    
    /**
     * It reads all files into the folder defined in PATH_BUS_ROUTE_FOLDER
     * and return an Array that contains buses routes.
     * 
     * @return Array An array that contain all buses lines into the folder
     * with its routes.
     */
    protected function setUpBusRoute()
    {
        $arrBusRoute = array();
        $arrFiles = scandir(PATH_BUS_ROUTE_FOLDER);
        
        foreach ($arrFiles as $fileName) {
            if (is_file(PATH_BUS_ROUTE_FOLDER.$fileName)) {
                $arrBusRoute[] = $this->getBusRouteByFilename($fileName);
            }
        }
        
        return $arrBusRoute;
    }
    
    /**
     * It read the specified filename and returns an array that contain
     * a bus line with its specific route.
     * 
     * @param String $filename The name of file to read.
     * @return Array An array that contain the file information.
     *      Array (String linea, String ramal, String zona, Array route)
     */
    protected function getBusRouteByFilename($filename)
    {
        $arrPoints = array();
        $this->xmlObject = simplexml_load_file(PATH_BUS_ROUTE_FOLDER.$filename, null, LIBXML_NOCDATA);
        
        foreach($this->xmlObject->Document->Folder as $node) {
            
            if(strtoupper($node->name) == "LINEA") {
                $line = (int) $node->value;
            } elseif(strtoupper($node->name) == "RAMAL") {
                $ramal = (string) $node->value;
            } elseif(strtoupper($node->name) == "ZONA") {
                $zone = (string) $node->value;
            } elseif(strtoupper($node->name) == "RECORRIDO") {
                $order = 10;
                
                foreach($node->Placemark as $placemark) {
                    $coordenadas = explode(",0",$placemark->LineString->coordinates);
                    
                    foreach($coordenadas as $pointCoord) {
                        $point = new \stdClass;
                        $coordPoint = explode(",", trim($pointCoord));
                        
                        if (isset($coordPoint[1])) {
                            $point->latitud    = (double)$coordPoint[1];
                            $point->longitud   = $coordPoint[0];
                            $point->orden      = $order;

                            $arrPoints[] = $point;
                            $order += 10;
                        }
                    }
                }
            } 
        }
        
        return Array (
            "linea"     =>  $line,
            "ramal"     =>  $ramal,
            "zona"      =>  $zone,
            "route"     =>  $arrPoints
        );
    }
}