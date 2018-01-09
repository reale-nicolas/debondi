<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ConstantsTableSeeder::class);
        $this->call(BusStopsTableSeeder::class);
        $this->call(BusLinesTableSeeder::class);
        $this->call(BusLinesBusStopsTableSeeder::class);
    }
}