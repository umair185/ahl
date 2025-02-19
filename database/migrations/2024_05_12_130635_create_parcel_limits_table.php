<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParcelLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parcel_limits', function (Blueprint $table) {
            $table->id();
            $table->integer('city_id')->nullable();
            $table->integer('limit')->nullable();
            $table->timestamp('last_update_on')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('parcel_limit')->nullable()->after('order_reference');
            $table->integer('parcel_attempts')->nullable()->after('parcel_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parcel_limits');
    }
}
