<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrator with full access',
            ],
            [
                'name' => 'buyer',
                'description' => 'Can create RFQs and place orders',
            ],
            [
                'name' => 'seller',
                'description' => 'Can list products and respond to RFQs',
            ],
            [
                'name' => 'inspector',
                'description' => 'Can perform product inspections',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
} 