<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIntervalDaysToGbsRouteDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gbs_route_days', function (Blueprint $table) {
            $table->unsignedSmallInteger('interval_days')->default(7)->after('day_of_week');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gbs_route_days', function (Blueprint $table) {
            $table->dropColumn('interval_days');
        });
    }
}

