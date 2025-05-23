<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderProfile extends Model
{
    use HasFactory;

    protected $table = 'provider_profiles';

    protected $fillable = [
        'user_id',
        'skills',
        'experience_years',
        'rating',
        'bio',
        'location'
    ];

    public function jobTypes()
    {
        return $this->belongsToMany(JobType::class, 'provider_profile_job_type');
    }

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
