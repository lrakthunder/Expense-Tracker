<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed a known account used by Cypress login test
        User::updateOrCreate(
            ['email' => 'lrakthunder@gmail.com'],
            [
                'first_name' => 'Karl Gilbert',
                'last_name' => 'Pascual',
                'username' => 'lrakthunder',
                'password' => 'Gwapoakodiba_123', // hashed automatically by cast
            ]
        );
    }
}
