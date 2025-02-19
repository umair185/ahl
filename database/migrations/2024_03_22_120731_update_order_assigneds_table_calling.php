<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrderAssignedsTableCalling extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_assigneds', function (Blueprint $table) {
            $table->string('cdrid')->nullable()->after('remarks_status');
            $table->string('call_response')->default(0)->after('cdrid');
            $table->string('call_input')->default(0)->after('call_response');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_assigneds', function (Blueprint $table) {
            //
        });
    }
}
