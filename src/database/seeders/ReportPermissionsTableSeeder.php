<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'title' => 'report_create',
            ],
            [
                'title' => 'report_edit',
            ],
            [
                'title' => 'report_show',
            ],
            [
                'title' => 'report_delete',
            ],
            [
                'title' => 'report_access',
            ],
        ];
        Permission::insert($permissions);
    }
}
