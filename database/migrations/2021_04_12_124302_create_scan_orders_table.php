<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScanOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scan_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pickup_request_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('picker_id');
            $table->unsignedBigInteger('middle_man_id')->nullable();
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->timestamps();

            $table->foreign('pickup_request_id')->references('id')->on('pickup_requests');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('picker_id')->references('id')->on('users');
            $table->foreign('middle_man_id')->references('id')->on('users');
            $table->foreign('supervisor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scan_orders');
    }
}
