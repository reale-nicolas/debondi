<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableBusLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bus_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('number');
            $table->string('letter', 1);
            $table->string('zone');
            $table->text('interest_points')->nullable();
            $table->text('neighborhoods')->nullable();
            $table->text('literal_path')->nullable();
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
        Schema::dropIfExists('bus_lines');
    }
}
