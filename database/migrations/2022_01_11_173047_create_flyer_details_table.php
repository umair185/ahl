<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlyerDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flyer_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('flyer_id');
            $table->string('quantity')->nullable();
            $table->string('flyer_price')->nullable();
            $table->string('flyer_total')->nullable();
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('flyer_requests');
            $table->foreign('flyer_id')->references('id')->on('flyers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flyer_details');
    }
}
