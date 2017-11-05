<?php

use App\XML\XMLBusStopsParser;
use Illuminate\Database\Seeder;

class BusLinesTableSeeder extends Seeder
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
        echo "\n Iniciando volcado de datos tabla BUS_LINES...";
        
        $xmlBusLines   = new XMLBusStopsParser();
        $arrBusLines   = $xmlBusLines->getBusLines();
                
        foreach ($arrBusLines as $line) {
            
            echo "\n Importando bus line name: ".$line['line'].$line['ramal']."-".$line['zone'];
            
            $busLinesRepository = App::make("App\Http\Controllers\BusLinesController");
            $res = $busLinesRepository->create($line);
//            $res = $busLinesRepository->insert($line['line'], $line['ramal'], $line['zone']);
            
            if (!$res) {
                $error = true;
                echo "\n\n Error en registro: ".$line['line'].$line['ramal']."-".$line['zone'];
            }
        }
        
        if($error) {
            echo "\n ¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬";
            echo "\n Ocurrio un ERROR en al menos uno de los registros";
            echo "\n ¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬";
        }
        
        echo "\n ...Finalizando volcado de datos tabla BUS_LINES";
        echo "\n\n\n";
    }
}
