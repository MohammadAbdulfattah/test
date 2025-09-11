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
            $table->text('enabled_user_modules')
                ->after('enabled_purchases_modules')
                ->nullable();
            $table->text('enabled_expenses_modules')
                ->after('enabled_user_modules')
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
            $table->dropColumn('enabled_user_modules');
            $table->dropColumn('enabled_expenses_modules');
        });
    }
};
