<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('reasons')->insert([
            [
                'name' => 'In Complete Address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bad Address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Close On Arival',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Consignee Not Available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unattempted',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Delivered',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Refused Delivery',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        Schema::create('sub_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reason_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('reason_id')->references('id')->on('reasons');
        });

        DB::table('sub_reasons')->insert([
            //reason 2 Bad Address
            [
                'reason_id' => 2,
                'name' => 'No Such House/Flat Exists',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reason_id' => 2,
                'name' => 'No Such Building/Plot Exists',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reason_id' => 2,
                'name' => 'No Such Office/Shop Exists',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //reason 4 Consignee Not Available
            [
                'reason_id' => 4,
                'name' => 'Consignee Not Available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reason_id' => 4,
                'name' => 'Blood Relation Not Available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reason_id' => 4,
                'name' => 'No Such Consignee Exists',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reason_id' => 4,
                'name' => 'Consignee Out Of City',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reason_id' => 4,
                'name' => 'Consignee Shifted',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::table('order_assigneds', function (Blueprint $table) {
            $table->unsignedBigInteger('cancel_reason_id')->nullable()->after('trip_status_id');

            $table->foreign('cancel_reason_id')->references('id')->on('reasons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reasons');
    }
}
