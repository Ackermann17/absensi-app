<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@absensi.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'employee_code' => 'ADM001',
                'phone' => null,
                'position' => 'Administrator',
                'status' => 'active',
            ]
        );
    }
}