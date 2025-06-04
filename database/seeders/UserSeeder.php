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
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Dosen Informatika',
                'email' => 'dosen@example.com',
                'password' => Hash::make('password'),
                'role' => 'dosen',
            ],
            [
                'name' => 'Mahasiswa Teknik',
                'email' => 'mahasiswa@example.com',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
