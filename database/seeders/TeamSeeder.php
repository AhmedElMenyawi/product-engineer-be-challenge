<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Team::create([
            'name' => 'The Avengers',
            'description' => 'The Earth Heroes',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Team::create([
            'name' => 'Justice League',
            'description' => 'Guardians of Earth from the DC universe',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
