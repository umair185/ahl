<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableRiderCash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rider_cash_collections', function (Blueprint $table) {
            $table->string('in_cash_collection')->default(0)->after('remaining_amount');
            $table->string('ibft_collection')->default(0)->after('in_cash_collection');
            $table->string('ibft_comment')->nullable()->after('ibft_collection');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rider_cash_collections', function (Blueprint $table) {
            //
        });
    }
}
