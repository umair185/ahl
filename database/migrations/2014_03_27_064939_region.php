<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Region extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
        });

        DB::table('regions')->insert(['name' => 'Lahore',]);
        DB::table('regions')->insert(['name' => 'Karachi',]);
        DB::table('regions')->insert(['name' => 'Islamabad',]);
        DB::table('regions')->insert(['name' => 'Peshawar',]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }

}
