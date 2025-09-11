<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('van_stock', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('product_id')->unsigned()->nullable();
            $table->integer('product_variation_id')->unsigned()->comment('id from product_variations table')->nullable();

            $table->integer('variation_id')->unsigned()->nullable();
            $table->foreign('variation_id')->references('id')->on('variations');

            $table->bigInteger('van_id')->unsigned();
            $table->foreign('van_id')->references('id')->on('vans');

            $table->decimal('qty_available', 22, 4)->default(0);

            $table->timestamps();

            //Indexing
            $table->index('product_id');
            $table->index('product_variation_id');
            $table->index('variation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('van_stock');
    }
};
