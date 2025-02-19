<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrantorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grantors', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('grantor_relation')->nullable();
            $table->string('grantor_name')->nullable();
            $table->string('grantor_father_name')->nullable();
            $table->string('grantor_cnic')->nullable();
            $table->string('grantor_age')->nullable();
            $table->string('grantor_address',1000)->nullable();
            $table->string('grantor_pin_location')->nullable();
            $table->string('grantor_house')->nullable();
            $table->string('grantor_job')->nullable();
            $table->string('grantor_income')->nullable();
            $table->string('grantor_phone')->nullable();
            $table->string('grantor_relation_two')->nullable();
            $table->string('grantor_name_two')->nullable();
            $table->string('grantor_father_name_two')->nullable();
            $table->string('grantor_cnic_two')->nullable();
            $table->string('grantor_age_two')->nullable();
            $table->string('grantor_address_two',1000)->nullable();
            $table->string('grantor_pin_location_two')->nullable();
            $table->string('grantor_house_two')->nullable();
            $table->string('grantor_job_two')->nullable();
            $table->string('grantor_income_two')->nullable();
            $table->string('grantor_phone_two')->nullable();

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
        Schema::dropIfExists('grantors');
    }
}
