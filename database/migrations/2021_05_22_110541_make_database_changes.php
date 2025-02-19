<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeDatabaseChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
            //$table->unsignedBigInteger('user_id')->after('created_by');
            $table->unsignedBigInteger('user_id')->nullable()->after('created_by');
            $table->foreign('user_id')->references('id')->on('users');

            //drop column from user detail table
            $table->dropForeign('user_details_country_id_foreign');
            $table->dropForeign('user_details_city_id_foreign');
            $table->dropForeign('user_details_state_id_foreign');
            $table->dropColumn('country_id');
            $table->dropColumn('city_id');
            $table->dropColumn('state_id');
        });

        Schema::table('users', function (Blueprint $table) {
            //drop column from user table
            $table->dropForeign('users_user_detail_id_foreign');
            $table->dropColumn('user_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
