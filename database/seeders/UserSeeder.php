<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@fitnesstracker.test'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password123'),
            ]
        );
    }
}