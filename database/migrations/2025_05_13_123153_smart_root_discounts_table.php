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
            if (!Schema::hasTable('smart_root_discounts')) {
            Schema::create('smart_root_discounts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->json('business_id');
                $table->json('user_id');
                $table->dateTime('start_date');
                $table->dateTime('end_date');
                $table->integer('type_smart_root_discount_id')->unsigned();
                $table->foreign('type_smart_root_discount_id')->references('id')->on('type_smart_root_discount')->onDelete('cascade');
                $table->integer('created_by')->unsigned()->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
