<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pickup_request_id');
            $table->unsignedBigInteger('picker_id');
            $table->integer('status')->default(1);
            $table->timestamps();

            $table->foreign('pickup_request_id')->references('id')->on('pickup_requests');
            $table->foreign('picker_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assign_requests');
    }
}
