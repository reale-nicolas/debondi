<?php

namespace App\Models;


class BusLines extends BaseModel
{
    
    public function stops()
    {
        return $this->belongsToMany('App\Models\BusStops', 'bus_lines_bus_stops', 'id_bus_line', 'id_bus_stop');

    }
    
}
