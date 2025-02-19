<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('vendor_name');
            $table->string('vendor_phone');
            $table->string('vendor_email');
            $table->string('vendor_address');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('focal_person_name');
            $table->string('focal_person_phone');
            $table->string('focal_person_address');
            $table->string('focal_person_email');
            $table->string('cnic');
            $table->string('ntn')->nullable();
            $table->string('strn')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_title')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('website');
            $table->string('logo')->nullable();
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('region_id');
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('vendors');
    }
}
