<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParcelNaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parcel_natures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->Integer('status')->default(1);
            $table->timestamps();
        });

        DB::table('parcel_natures')->insert([
            [
                'name' => 'Normal Parcel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reverse Pickup',
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
        Schema::dropIfExists('parcel_natures');
    }
}
