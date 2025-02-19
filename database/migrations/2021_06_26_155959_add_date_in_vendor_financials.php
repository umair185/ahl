<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateInVendorFinancials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_financials', function (Blueprint $table) {
            $table->date('date_from')->after('ahl_commission')->nullable();
            $table->date('date_to')->after('ahl_commission')->nullable();
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
