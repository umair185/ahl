<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        Schema::create('packings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('consignee_first_name');
            $table->string('consignee_last_name');
            $table->string('consignee_email')->nullable();
            $table->string('consignee_address');
            $table->string('consignee_phone');
            $table->unsignedBigInteger('consignee_country');
            $table->unsignedBigInteger('consignee_state');
            $table->unsignedBigInteger('consignee_city');

            $table->string('consignment_order_id');
            $table->unsignedBigInteger('consignment_order_type');
            $table->string('consignment_cod_price');
            $table->string('consignment_weight');
            $table->unsignedBigInteger('consignment_packaging');
            $table->string('consignment_pieces');
            $table->string('consignment_description')->nullable();
            $table->unsignedBigInteger('consignment_origin_city');
            $table->unsignedBigInteger('order_status')->default(1);

            //$table->string('additional_services_type');
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
        Schema::dropIfExists('orders');
    }
}
