<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderInSagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_in_sags', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->nullable();
            $table->integer('sag_id')->nullable();
            $table->integer('status')->nullable();
            $table->integer('from')->nullable();
            $table->integer('to')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('sag_status')->default(0)->after('order_status');
            $table->integer('sag_id')->nullable()->after('sag_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_in_sags');
    }
}
