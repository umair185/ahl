<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOldReasonWoking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('order_assigneds', function (Blueprint $table) {
            $table->dropColumn('cancel_reason');
        });

        Schema::table('sub_reasons', function (Blueprint $table) {
            $table->dropForeign('sub_reasons_reason_id_foreign');
        });

        Schema::drop('reasons');
        Schema::drop('sub_reasons');

        //Order Decline Reasons
        Schema::create('order_decline_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('order_decline_reasons')->insert([
            [
                'name' => 'Incomplete Address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Consignee Not Available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Refused To Receive The Parcel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Insuffcient Funds',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        //Order Decline Status
        Schema::create('order_decline_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('order_decline_statuses')->insert([
            [
                'name' => 'Cancelled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reattempt',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        //Order Decline
        Schema::create('order_declines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_assigned_id');
            $table->unsignedBigInteger('order_decline_status_id');
            $table->unsignedBigInteger('order_decline_reason_id');
            $table->string('additional_note');
            $table->string('image');
            $table->timestamps();

            $table->foreign('order_assigned_id')->references('id')->on('order_assigneds');
            $table->foreign('order_decline_status_id')->references('id')->on('order_decline_statuses');
            $table->foreign('order_decline_reason_id')->references('id')->on('order_decline_reasons');
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
