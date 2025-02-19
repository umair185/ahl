<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScanDateInScanOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scan_orders', function (Blueprint $table) {
            $table->dateTime('middle_man_scan_date')->after('middle_man_id')->nullable();
            $table->dateTime('supervisor_scan_date')->after('supervisor_id')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dateTime('dispatch_date')->after('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scan_orders', function (Blueprint $table) {
            //
        });
    }
}
