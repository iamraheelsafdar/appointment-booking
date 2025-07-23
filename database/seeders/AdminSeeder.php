<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => fake()->name,
            'user_type' => 'Admin',
            'email' => fake()->email,
            'password' => Hash::make('123456'),
            'status' => 1
        ]);
    }
}
