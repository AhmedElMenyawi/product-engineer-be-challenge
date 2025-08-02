<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Anthony',
            'last_name' => 'Poncio',
            'email' => 'anthonyponcio@hellchef.com',
            'password' => Hash::make('AP@Random123')
        ]);

        User::create([
            'first_name' => 'Mohsin',
            'last_name' => 'Ali',
            'email' => 'mohsinali@hellchef.com',
            'password' => Hash::make('MA@Random123')
        ]);

        User::create([
            'first_name' => 'Ahmed',
            'last_name' => 'ElMenyawi',
            'email' => 'ahmedelmenyawi@hellchef.com',
            'password' => Hash::make('AE@Random123')
        ]);
        
        
    }
}
