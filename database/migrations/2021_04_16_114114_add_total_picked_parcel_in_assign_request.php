<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalPickedParcelInAssignRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assign_requests', function (Blueprint $table) {
            $table->string('total_picked_parcel')->default(0)->after('picker_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assign_requests', function (Blueprint $table) {
            $table->string('total_picked_parcel')->default(0)->after('picker_id');
        });
    }
}
