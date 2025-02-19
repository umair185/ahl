<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelReasonColumnInOrderAssignedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_assigneds', function (Blueprint $table) {
            $table->dropForeign('order_assigneds_cancel_reason_id_foreign');
            $table->dropColumn('cancel_reason_id');

            $table->json('cancel_reason')->nullable()->after('trip_status_id');
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
