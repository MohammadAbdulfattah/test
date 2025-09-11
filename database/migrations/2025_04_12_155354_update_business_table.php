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
        Schema::table('business', function (Blueprint $table) {
            $table->text('enabled_products_modules')
                ->after('enabled_modules')
                ->nullable();
            $table->text('enabled_sale_modules')
                ->after('enabled_products_modules')
                ->nullable();
            $table->text('enabled_purchases_modules')
                ->after('enabled_sale_modules')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business', function (Blueprint $table) {
            $table->dropColumn('enabled_products_modules');
            $table->dropColumn('enabled_sale_modules');
            $table->dropColumn('enabled_purchases_modules');
        });
    }
};
