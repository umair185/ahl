<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });

        // Insert some stuff
        DB::table('statuses')->insert([
            [
                'name' => 'Awaiting Pickup',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pickup',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'At AHL Warehouse',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Check By Supervisor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dispatched',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Delivered',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Request For Reattempt',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reattempt',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cancelled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Returned To Vendor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cancelled By AHL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cancelled By Vendor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Void Label',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statuses');
    }
}
