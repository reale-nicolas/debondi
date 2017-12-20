<?php


function distanceBetweenTerrainPoints($punto1_lat, $punto1_long, $punto2_lat, $punto2_long, $unidad = "km", $decimales = 2)
{
//    echo "<pre>";print_r($punto1_lat);echo "</pre>";
    $grados = rad2deg(acos((sin(deg2rad($punto1_lat))*sin(deg2rad($punto2_lat))) + 
            (cos(deg2rad($punto1_lat))*cos(deg2rad($punto2_lat))*cos(deg2rad($punto1_long-$punto2_long)))));
            
    switch($unidad){
        case "km":
            $distancia = $grados * 111.13384;
            break;
        case "m":
            $distancia = $grados * 111.13384 * 1000;
            break;
        case "mi":
            $distancia = $grados * 69.05482;
            break;
        case "nmi":
            $distancia = $grados * 59.97662;
            break;
    }
    
    return round($distancia, $decimales);
}