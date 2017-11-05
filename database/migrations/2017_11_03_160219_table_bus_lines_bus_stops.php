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
            $table->integer('id_bus_line')->unsigned();
            $table->integer('id_bus_stop')->unsigned();
            $table->smallInteger('order');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
        
        Schema::table('bus_lines_bus_stops', function($table) {
            $table  ->foreign('id_bus_line')
                    ->references('id') 
                    ->on('bus_lines')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->index('id_bus_line');
            
            $table  ->foreign('id_bus_stop')
                    ->references('id')
                    ->on('bus_stops')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->index('id_bus_stop');
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
