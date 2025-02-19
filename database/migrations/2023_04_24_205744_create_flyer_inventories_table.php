<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlyerInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flyer_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flyer_id');
            $table->integer('qty')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('flyer_id')->references('id')->on('flyers');
        });

        Schema::table('flyers', function (Blueprint $table) {
            $table->integer('current_stock')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flyer_inventories');
    }
}
