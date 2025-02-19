<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->string('father_name')->nullable();
            $table->string('father_cnic')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('permanent_staff_address',1000)->nullable();
            $table->string('pin_location')->nullable();
            $table->string('bike_number')->nullable();
            $table->string('payment_cheque')->nullable();
            $table->string('siblings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_details', function (Blueprint $table) {
            //
        });
    }
}
