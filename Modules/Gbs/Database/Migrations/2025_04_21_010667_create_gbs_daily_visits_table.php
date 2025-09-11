<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGbsDailyVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gbs_daily_visits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');     
            $table->integer('reason_id')->nullable()->unsigned();
            $table->foreign('reason_id')->references('id')->on('gbs_failure_reasons')->onDelete('cascade');   
            $table->integer('contact_id')->unsigned();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->integer('route_day_id')->unsigned();
            $table->foreign('route_day_id')->references('id')->on('gbs_route_days')->onDelete('cascade');
            $table->timestamp('started_at')->nullable(); 
            $table->timestamp('ended_at')->nullable();
            $table->decimal('user_latitude', 10, 7)->nullable();  
            $table->decimal('user_longitude', 10, 7)->nullable();
            $table->string('notes');
            $table->enum('status', ['sale_made', 'not_sale'])->default('not_sale');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gbs_daily_visits');
    }
}
