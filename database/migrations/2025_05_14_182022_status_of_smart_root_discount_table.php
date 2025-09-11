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
         if (!Schema::hasTable('status_of_smart_root_discount')) {
            Schema::create('status_of_smart_root_discount', function (Blueprint $table) {
                $table->increments('id');
                $table->decimal('invoice_amount', 22, 4)->default(0);
                $table->decimal('discount_amount', 22, 4)->default(0);
                $table->integer('smart_root_discount_id')->unsigned();
                $table->foreign('smart_root_discount_id')->references('id')->on('smart_root_discounts')->onDelete('cascade');
                $table->integer('discount_status_id')->unsigned()->nullable();
                $table->foreign('discount_status_id')->references('id')->on('discount_status')->onDelete('cascade');
                $table->integer('final_discount_id')->unsigned()->nullable();
                $table->foreign('final_discount_id')->references('id')->on('final_discount')->onDelete('cascade');
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
