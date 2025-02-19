<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParcelSagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parcel_sags', function (Blueprint $table) {
            $table->id();
            $table->string('sag_number')->nullable();
            $table->integer('sag_count')->nullable();
            $table->integer('status')->default(0);
            $table->integer('close_by')->nullable();
            $table->integer('open_by')->nullable();
            $table->integer('close_in')->nullable();
            $table->integer('open_in')->nullable();
            $table->integer('created_by')->nullable();
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
        Schema::dropIfExists('parcel_sags');
    }
}
