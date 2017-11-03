<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableBusLinesBusStops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bus_lines_bus_stops', function (Blueprint $table) 
        {
            $table->integer('id_bus_line');
            $table->foreign('id_bus_line')->references('id')->on('bus_lines');
            $table->index('id_bus_line');
            
            $table->integer('id_bus_stop');
            $table->foreign('id_bus_stop')->references('id')->on('bus_stops');
            $table->index('id_bus_stop');
            
            $table->smallInteger('order');
            $table->boolean('enabled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bus_lines_bus_stops');
    }
}
