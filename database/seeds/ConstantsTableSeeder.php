<?php

use App\Models\Constants;
use Illuminate\Database\Seeder;

class ConstantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "\n\n\n";
        echo "\n Iniciando volcado de datos tabla CONSTANTS...";
        
        $error = false;
        $arrConstants = $this->getConstants();
        
        foreach ($arrConstants as $constant) {
            
            echo "\n Importando constante: ".$constant['name'];
            
            $res = Constants::create([
                "name"  => $constant['name'],
                "value" => $constant['value']
            ]);
            
            if (!$res) {
                $error = true;
            }
        }
        
        if($error) {
            echo "\n ¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬";
            echo "\n Ocurrio un ERROR en al menos uno de los registros";
            echo "\n ¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬";
        }
        
        echo "\n ...Finalizando volcado de datos tabla CONSTANTS";
        echo "\n\n\n";
    }
    
    
    private function getConstants()
    {
        $constants = Array(
            array("name" => "PATH_XML_RESOURCE_FOLDER",        "value" => base_path()."/resources/xml/bus_routes/"),
            array("name" => "PATH_BUS_STOP_FOLDER",           "value" => __DIR__."/resources/xmlBusStop/"),
            array("name" => "CTE_PESO_CAMINO_A_PIE",          "value" => "10"),
            array("name" => "CTE_PESO_CAMBIO_COLECTIVO",      "value" => "10")
        );
        
        return $constants;
    }
}
