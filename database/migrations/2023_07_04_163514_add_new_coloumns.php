<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColoumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_financials', function (Blueprint $table) {
            $table->string('advance_amount')->nullable()->after('amount');
            $table->string('deduction_remarks')->default('normal')->after('deduction_amount');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->double('advance',11,2)->default(0)->after('vendor_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_financials', function (Blueprint $table) {
            //
        });
    }
}
