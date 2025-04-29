<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProviderProfile;
use App\Models\JobType;

class ProviderProfileJobTypeSeeder extends Seeder
{
    public function run()
    {
        $provider1 = ProviderProfile::find(10);
        $provider2 = ProviderProfile::find(11);

        $jobTypePlumbing = JobType::find(4);
        $jobTypeElectrical = JobType::find(5);

        if ($provider1 && $jobTypePlumbing) {
            $provider1->jobTypes()->attach($jobTypePlumbing->id);
        }

        if ($provider1 && $jobTypeElectrical) {
            $provider1->jobTypes()->attach($jobTypeElectrical->id);
        }

        if ($provider2 && $jobTypePlumbing) {
            $provider2->jobTypes()->attach($jobTypePlumbing->id);
        }
    }
}
