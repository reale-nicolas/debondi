<?php

use Illuminate\Database\Seeder;

class BusLinesBusStopsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $error = false;
        
        echo "\n\n\n";
        echo "\n Iniciando volcado de datos tabla BUS_LINES_BUS_STOPS...";
        
        $xmlBusLines   = new App\XML\XMLBusStopsParser;
        $arrBusLines   = $xmlBusLines->getBusLines();
                
        $busStopsRepository = App::make("App\Repositories\BusStopsRepository");
        $busLinesRepository = App::make("App\Repositories\BusLinesRepository");
        
        $busStopsAll = $busStopsRepository->all();
        $busLinesBusStopsRepository = App::make("App\Http\Controllers\BusLinesBusStopsController");
        
        foreach ($arrBusLines as $line) 
        {
            $arrUnfoundedBusStops = array();
            $founded = $unfounded = 0;
            echo "\n\n\n\nLinea: ".$line['line']." - Ramal: ".$line['ramal']." - Zone: ".$line['zone'];
            
            //Buscamos la linea de colectivo obtenida del XML en la base de datos para obtener su ID.
            $dbBusLine = $busLinesRepository->getLines($line['line'], $line['ramal'], $line['zone']);
            $idLine = $dbBusLine->id;
            
            //Obtenemos la ruta de la linea de colectivos del XML
            $arrBusStops   = $xmlBusLines->getBusRouteStops($line['line'], $line['ramal'], $line['zone']);
            foreach($arrBusStops as $k => $busStop)
            {
                if (is_numeric($k)) 
                {
//                    echo "\n ";
                    $dbBusStop = $busStopsRepository->getBusStopsByLatLng($busStop['latitude'], $busStop['longitude']);
                    
                    if ($dbBusStop)
                    {
                        $idStop = (isset($dbBusStop->id)? $dbBusStop->id : null);

//                        echo "\n Id Line: ".$idLine." - Id Stop: ".$idStop." - Count: ".$dbBusStop;
                        $founded++;
                        
                    } else {
                        $unfounded++;
                        $arrDistancias = $this->harvestine(
                            $busStopsAll, 
                            $busStop['latitude'], 
                            $busStop['longitude']
                        );
//                        echo "\n Distancia: ".$arrDistancias[0];
//                        echo "\n Xml Coords: (".$arrDistancias[1].",".$arrDistancias[2].")";
//                        echo "\n DB  Coords: (".$busStop['latitude'].",".$busStop['longitude'].")";
                        
                        $dbBusStop = $busStopsRepository->getBusStopsByLatLng($arrDistancias[1], $arrDistancias[2]);
                        if ($dbBusStop) 
                            $idStop = (isset($dbBusStop->id)? $dbBusStop->id : null);
                    }
                    $order =$busStop['order'];
                    
                    $insertResult = $busLinesBusStopsRepository->create(
                            array(
                                "id_bus_line" => $idLine, 
                                "id_bus_stop" => $idStop, 
                                "order"       => $order
                            )
                    );
                    
                    if ($insertResult)
                    {                    
                        echo "\n Id Orden: ".$order." - Id Stop: ".$idStop." - Count: ".$dbBusStop;
                    } else {
                        echo "\n ERROR!!!! Id Orden: ".$order." - Id Stop: ".$idStop." - Count: ".$dbBusStop;
                    }
                }
            }
            echo "\n\n Encontrados: ".$founded." - No encontrados: ".$unfounded;
        }
        
        if($error) {
            echo "\n ¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬";
            echo "\n Ocurrio un ERROR en al menos uno de los registros";
            echo "\n ¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬";
        }
        
        echo "\n ...Finalizando volcado de datos tabla BUS_LINES_BUS_STOPS";
        echo "\n\n\n";
    }
    
    
    function harvestine($parada, $lat1, $long1)
    { 
        $menorDistancia = 10000000;
        $lat = $long = null;
        //Distancia en kilometros en 1 grado distancia.
        //Distancia en millas nauticas en 1 grado distancia: $mn = 60.098;
        //Distancia en millas en 1 grado distancia: 69.174;
        //Solo aplicable a la tierra, es decir es una constante que cambiaria en la luna, marte... etc.
        $km = 111.302;

        //1 Grado = 0.01745329 Radianes    
        $degtorad = 0.01745329;

        //1 Radian = 57.29577951 Grados
        $radtodeg = 57.29577951; 
        //La formula que calcula la distancia en grados en una esfera, llamada formula de Harvestine. Para mas informacion hay que mirar en Wikipedia
        //http://es.wikipedia.org/wiki/F%C3%B3rmula_del_Haversine
        
        foreach($parada as $p)
        {
            $lat2 = $p->latitude; 
            $long2 = $p->longitude; 
            
            $dlong = ($long1 - $long2); 
            $dvalue = (sin($lat1 * $degtorad) * sin($lat2 * $degtorad)) + (cos($lat1 * $degtorad) * cos($lat2 * $degtorad) * cos($dlong * $degtorad)); 
            $dd = acos($dvalue) * $radtodeg; 
            
            $distancia =  ($dd * $km)*1000;
            
            if($distancia < $menorDistancia)
            {
                $menorDistancia = $distancia;
                $lat = $lat2;
                $long = $long2;
            }
        }
        
        return array($menorDistancia, $lat, $long);
        
    }
}
