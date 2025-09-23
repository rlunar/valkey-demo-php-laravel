<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@valkey.io',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Create some regular users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@valkey.io',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@valkey.io',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Mike Johnson',
            'email' => 'mike@valkey.io',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Create additional random users
        User::factory(6)->create();
    }
}