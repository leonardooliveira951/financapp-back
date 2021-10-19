<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ColorsSeeders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('colors')->insert([
            ['id' => 1, 'name' => 'blue', 'hex_code' => '#4267B2'],
            ['id' => 2, 'name' => 'cyan', 'hex_code' => '#1DB4F5'],
            ['id' => 3, 'name' => 'green', 'hex_code' => '#20B74A'],
            ['id' => 4, 'name' => 'yellow', 'hex_code' => '#9DB410'],
            ['id' => 5, 'name' => 'orange', 'hex_code' => '#F5781D'],
            ['id' => 6, 'name' => 'red', 'hex_code' => '#F51D1D'],
            ['id' => 7, 'name' => 'violet', 'hex_code' => '#961DF5'],
            ['id' => 8, 'name' => 'light-violet', 'hex_code' => '#C289EF'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
