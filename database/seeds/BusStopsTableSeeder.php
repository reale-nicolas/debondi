<?php

use App\XML\XMLBusStopsParser;
use Illuminate\Database\Seeder;

class BusStopsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $error = false;
        $errors = array();
        echo "\n\n\n";
        echo "\n Iniciando volcado de datos tabla BUS_STOPS...";
        
        $xmlBusStop   = new XMLBusStopsParser();
        $arrBusStop         = $xmlBusStop->getBusStops();
                
        $orden = 0;
        foreach ($arrBusStop as $stop) {
            
            echo "\n Importando Stop Nro: ".$orden." - Name: ".$stop['name'];
            
            
            $busStopsRepository = App::make("App\Http\Controllers\BusStopsController");
            $res = $busStopsRepository->create($stop);
//            $res = $busStopsRepository->insert($stop['name'], $stop['latitude'], $stop['longitude']);
            
            if (!$res) {
                $error = true;
                $errors[] = $orden;
                echo "\n\n Error en registro nro: ".$orden;
            }
            
            $orden++;
        }
        
        if($error) {
            echo "\n ¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬";
            echo "\n Ocurrio un ERROR en al menos uno de los registros";
            echo "\n ¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬";
        }
        
        echo "\n ...Finalizando volcado de datos tabla BUS_STOPS";
        echo "\n\n\n";
    }
}
