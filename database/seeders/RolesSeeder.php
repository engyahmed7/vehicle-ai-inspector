<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['admin', 'agent', 'customer'];

        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role]);
        }

        $user = \App\Models\User::firstWhere('email', 'engy@gmail.com');
        if ($user) {
            Log::info($user);
            $user->assignRole('admin');
        }

        $customerUser = \App\Models\User::firstWhere('id', 2);
        if ($customerUser) {
            Log::info($customerUser);
            $customerUser->assignRole('customer');
            $customerUser->givePermissionTo('ManageKyc:Role');
        }
    }
}
