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
        Schema::table('stock_adjustment_lines', function (Blueprint $table) {
              if (!Schema::hasColumn('stock_adjustment_lines', 'transaction_sell_line_id')) {
                $table->integer('transaction_sell_line_id')->unsigned()->nullable();
                $table->foreign('transaction_sell_line_id')->references('id')->on('transaction_sell_lines')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_adjustment_lines', function (Blueprint $table) {
            //
        });
    }
};
