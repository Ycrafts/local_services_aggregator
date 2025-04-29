<?php

namespace Database\Seeders;

use App\Models\JobType;
use Illuminate\Database\Seeder;

class JobTypeSeeder extends Seeder
{
    public function run(): void
    {
        $jobTypes = [
            'Plumber',
            'Electrician',
            'Carpenter',
            'Painter',
            'Mechanic',
            'House Cleaner',
            'Gardener',
        ];

        foreach ($jobTypes as $type) {
            JobType::create([
                'name' => $type,
                'baseline_price' => rand(100, 500), // Random price between 100 and 500
            ]);
        }
    }
}
