<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDeliveredsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignee_relations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('consignee_relations')->insert([
            [
                'name' => 'Self',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Father',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mother',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sister',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Brother',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Son',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Daughter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Driver',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Watchman',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Husband',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wife',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::create('order_delivereds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_assigned_id');
            $table->string('amount');
            $table->unsignedBigInteger('consignee_relation_id');
            $table->string('other_relation')->nullable();
            $table->string('receiver_name');
            $table->string('cnic')->nullable();
            $table->string('comment')->nullable();
            $table->string('signature');
            $table->string('location_picture');
            $table->timestamps();

            $table->foreign('consignee_relation_id')->references('id')->on('consignee_relations');
            $table->foreign('order_assigned_id')->references('id')->on('order_assigneds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_delivereds');
    }
}
