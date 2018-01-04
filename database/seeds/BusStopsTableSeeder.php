<?php

//use App\XML\XMLBusStopsParser;
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
        
        $xmlBusStop   = new App\XML\XMLBusStopsParser;
        $arrBusStop   = $xmlBusStop->getBusStops();
                
        $busStopsRepository = App::make("App\Repositories\BusStopsRepository");
        
        $orden = 0;
        foreach ($arrBusStop as $stop) {
            
            echo "\n Importando Stop Nro: ".$orden." - Name: ".$stop['name'];
            
            $res = $busStopsRepository->create($stop);
            
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
