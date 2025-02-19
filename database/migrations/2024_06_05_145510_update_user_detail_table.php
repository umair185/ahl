<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->string('account_number')->nullable();
            $table->string('account_title')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('reporting_to')->nullable();
            $table->string('location')->nullable();
            $table->string('hiring_by')->nullable();
            $table->string('interviewed_by')->nullable();
            $table->string('hiring_platform')->nullable();
            $table->string('joining_date')->nullable();
            $table->string('leaving_date')->nullable();
            $table->string('company_assets')->nullable();
            $table->string('remarks')->nullable();
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
