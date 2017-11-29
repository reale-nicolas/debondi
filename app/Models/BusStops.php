<?php

namespace App\Models;


class BusStops extends BaseModel
{
   
    public function lines()
    {
        return $this->belongsToMany('App\Models\BusLines', 'bus_lines_bus_stops', 'id_bus_stop', 'id_bus_line');
    }
}
