<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderProfile extends Model
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'provider_profiles';

    // Fillable attributes for mass assignment
    protected $fillable = [
        'user_id',
        'skills',
        'experience_years',
        'rating',
        'bio',
        'location'
    ];

    // Relationship: A provider profile can have many job types
    public function jobTypes()
    {
        return $this->belongsToMany(JobType::class, 'provider_profile_job_type');
    }

    // Relationship: A provider can have many jobs they are assigned to
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function requestedJobs()
    {
        return $this->belongsToMany(Job::class, 'requested_jobs')
                    ->withPivot('is_interested')
                    ->withTimestamps();
    }

    // ProviderProfile model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
