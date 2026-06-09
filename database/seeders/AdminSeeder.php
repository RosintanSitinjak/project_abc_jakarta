<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Owner', 'email' => 'owner@lamsolusi.com', 'role' => \App\Enums\Role::Owner],
            ['name' => 'Admin', 'email' => '2381014@unai.edu', 'role' => \App\Enums\Role::Admin],
            ['name' => 'Writer', 'email' => 'writer@lamsolusi.com', 'role' => \App\Enums\Role::Writer],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => bcrypt('12345678'),
                    'role' => $user['role'],
                ]
            );
        }
    }
}
