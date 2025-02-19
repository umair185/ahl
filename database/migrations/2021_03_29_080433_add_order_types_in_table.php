<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderTypesInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('order_types')->insert([
            [
                'name' => 'COD',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NON COD',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('packings')->insert([
            [
                'name' => 'Flyer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table', function (Blueprint $table) {
            //
        });
    }
}
