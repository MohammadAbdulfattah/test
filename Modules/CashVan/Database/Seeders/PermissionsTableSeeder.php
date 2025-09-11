<?php

namespace Modules\CashVan\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [ 
            ['name' => 'cashvan.view'],
            ['name' => 'cashvan.create'],
            ['name' => 'cashvan.update'],
            ['name' => 'cashvan.delete'],
            ['name' => 'van_stock.create'],
            ['name' => 'van_stock.view'],
            ['name' => 'van_stock.view_history'],
            ['name' => 'van_stock.delete'],
        ];

        $insert_data = [];
        $time_stamp = \Carbon::now()->toDateTimeString();
        foreach ($data as $d) {
            $d['guard_name'] = 'web';
            $d['created_at'] = $time_stamp;
            $insert_data[] = $d;
        }
        Permission::insert($insert_data);
    }
}
