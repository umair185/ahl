<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRackBalancingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rack_balancings', function (Blueprint $table) {
            $table->id();
            $table->string('date_from')->nullable();
            $table->string('date_to')->nullable();
            $table->integer('total_parcels')->nullable();
            $table->integer('scan_parcels')->nullable();
            $table->string('mode')->nullable();
            $table->string('remarks')->nullable();
            $table->integer('remarks_by')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('previous_order_value')->nullable()->after('order_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rack_balancings');
    }
}
