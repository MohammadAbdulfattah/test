<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (!Schema::hasTable('final_discount')) {
            Schema::create('final_discount', function (Blueprint $table) {
                $table->increments('id');
                $table->json('sub_condition_ids')->nullable();
                $table->json('sub_result_ids')->nullable();
                $table->decimal('discount_amount')->default(0);
                $table->integer('discount_status_id')->unsigned()->nullable();
                $table->foreign('discount_status_id')->references('id')->on('discount_status')->onDelete('cascade');
                $table->timestamps();
            });
        }
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
};
