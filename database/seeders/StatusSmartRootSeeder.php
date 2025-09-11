<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\DiscountStatus;

class StatusSmartRootSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'ثابت'],
            ['name' => 'نسبة مئوية'],

        ];
        DiscountStatus::insert($data);
    }
}
