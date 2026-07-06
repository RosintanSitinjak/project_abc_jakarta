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
            [
            'name' => 'Admin ABC Jakarta', 
            'email' => 'admin@abcjakarta.com', 
            'role' => \App\Enums\Role::Admin // Tetap gunakan Enum
        ],

            [
            'name' => 'Pimpinan ABC Jakarta (Pak Andi)', 
            'email' => 'pimpinan@abcjakarta.com', 
            'role' => \App\Enums\Role::Owner // Role Owner/Pimpinan untuk Pak Andi
        ],

            [
            'name' => 'Intan Developer', 
            'email' => '2381014@unai.edu', // Akun pribadi untuk tes
            'role' => \App\Enums\Role::Admin
        ],

        [
            'name' => 'Karla Developer', 
            'email' => '2381044@unai.edu', // Akun pribadi untuk tes
            'role' => \App\Enums\Role::Admin
        ],
    ];


        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => bcrypt('12345678'), //password default
                    'role' => $user['role'],
                ]
            );
        }
    }
}
