<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrderAssignedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_assigneds', function (Blueprint $table) {
            $table->string('remarks')->after('status')->nullable();
            $table->integer('remarks_by')->after('remarks')->nullable();
            $table->integer('remarks_status')->after('remarks_by')->default(0);
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
