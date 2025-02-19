<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSagInBiltiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sag_in_bilties', function (Blueprint $table) {
            $table->id();
            $table->integer('bilty_id')->nullable();
            $table->integer('sag_id')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });

        Schema::table('parcel_sags', function (Blueprint $table) {
            $table->string('manual_seal_number')->nullable()->after('sag_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sag_in_bilties');
    }
}
