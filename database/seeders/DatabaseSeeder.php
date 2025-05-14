<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use ProviderProfileJobTypeSeeder;


class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        $this->call(JobTypeSeeder::class);
            // $this->call(ProviderProfileJobTypeSeeder::class);
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
