<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description');
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        
        DB::table('trip_statuses')->insert([
            [
                'name' => 'OR',
                'description' => 'Order Request',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'OS',
                'description' => 'Order Started',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'OD',
                'description' => 'Order Delivered',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'OCC',
                'description' => 'Order Cash Collected',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'OC',
                'description' => 'Order Cancelled',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        
        Schema::table('order_assigneds', function (Blueprint $table) {
            $table->dropColumn('trip_status_id');
        });
        
        Schema::table('order_assigneds', function (Blueprint $table) {
            $table->unsignedBigInteger('trip_status_id')->after('longitude')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_statuses');
    }
}
