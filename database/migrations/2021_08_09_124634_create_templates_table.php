<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
        });
        
        DB::table('templates')->insert(['name' => 'Order Creation SMS', 'subject' => 'Welcome to {{COMPANY_NAME}}', 'message'=>'Dear Mr/Miss {{MEMBER_NAME}} Your order has been assigned to {{COMPANY_NAME}} to Deliver it at your Door Step. Please Click on Below Link to Track your Order: {{APP_URL}} Sincerely, {{COMPANY_NAME}}']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('templates');
    }
}
