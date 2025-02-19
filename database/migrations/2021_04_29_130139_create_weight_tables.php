<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeightTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ahl_weights', function (Blueprint $table) {
            $table->id();
            $table->string('weight');
            $table->timestamps();
        });

        // Insert some stuff
        DB::table('ahl_weights')->insert([
            [
                'weight' => 'Up to 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weight' => 'Up to 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weight' => 'Up to 3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weight' => 'Up to 4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weight' => 'Up to 5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::create('vendor_weights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('ahl_weight_id');
            $table->string('price');
            $table->string('status');
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('ahl_weight_id')->references('id')->on('ahl_weights');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ahl_weights');
        Schema::dropIfExists('vendor_weights');
    }
}
