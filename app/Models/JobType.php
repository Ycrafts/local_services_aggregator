<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobType extends Model
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'job_types';

    // Fillable attributes for mass assignment
    protected $fillable = [
        'name',
        'baseline_price'
    ];

    // Relationship: A job type can have many jobs
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    // Relationship: A job type can be associated with many provider profiles
    public function providerProfiles()
    {
        return $this->belongsToMany(ProviderProfile::class, 'provider_profile_job_type');
    }
}
