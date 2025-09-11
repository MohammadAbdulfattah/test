<?php

namespace Modules\Gbs\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GbsFailureReasonsSeeder extends Seeder
{
    public function run()
    {
        DB::table('gbs_failure_reasons')->updateOrInsert(
            ['id' => 1],
            ['reason' => 'الزيارة لاحقاً', 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
