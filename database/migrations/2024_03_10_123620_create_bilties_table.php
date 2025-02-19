<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiltiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bilties', function (Blueprint $table) {
            $table->id();
            $table->string('bilty_number')->nullable();
            $table->string('manual_bilty_number')->nullable();
            $table->integer('from')->nullable();
            $table->integer('to')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('create_in')->nullable();
            $table->integer('open_in')->nullable();
            $table->integer('open_by')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });

        Schema::table('parcel_sags', function (Blueprint $table) {
            $table->tinyInteger('bilty_status')->default(0)->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bilties');
    }
}
