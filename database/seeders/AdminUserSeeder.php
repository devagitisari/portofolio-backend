<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Deva Gitisari',
            'email' => 'devagitisari96@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }
}
