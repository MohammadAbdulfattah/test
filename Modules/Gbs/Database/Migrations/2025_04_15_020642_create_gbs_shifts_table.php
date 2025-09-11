<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGbsShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gbs_shifts', function (Blueprint $table) {
            $table->id();

         
        
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->date('shift_date');        
            $table->time('start_time')->nullable(); 
            $table->decimal('start_latitude', 10, 7)->nullable();  
            $table->decimal('start_longitude', 10, 7)->nullable();
        
            $table->time('end_time')->nullable();   
            $table->decimal('end_latitude', 10, 7)->nullable();  
            $table->decimal('end_longitude', 10, 7)->nullable();   
        
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
        Schema::dropIfExists('gbs_shifts');
    }
}
