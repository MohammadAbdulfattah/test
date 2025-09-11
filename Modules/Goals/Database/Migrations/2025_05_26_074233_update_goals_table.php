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
        Schema::table('goals', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable();
            $table->foreign('group_id')->references('id')->on('goal_groups')->onDelete('cascade');
            $table->unsignedBigInteger('category_group_id')->nullable();
            $table->foreign('category_group_id')->references('id')->on('categories_group_goal')->onDelete('cascade');
            $table->unsignedBigInteger('brand_group_id')->nullable();
            $table->foreign('brand_group_id')->references('id')->on('brands_group_goal')->onDelete('cascade');
            $table->unsignedBigInteger('products_group_goal_id')->unsigned()->nullable();
            $table->foreign('products_group_goal_id')->references('id')->on('products_group_goal')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['brand_group_id']);
            $table->dropForeign(['category_group_id']);
            $table->dropColumn(['brand_group_id', 'category_group_id']);
        });
    }
};
