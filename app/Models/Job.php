<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $table = 'jobs';

    protected $fillable = [
        'user_id',
        'job_type_id',
        'description',
        'estimated_cost',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function requestedProviders()
    {
        return $this->belongsToMany(ProviderProfile::class, 'requested_jobs')
                    ->withPivot('is_interested')
                    ->withTimestamps();
    }

    public function assignedProvider()
    {
        return $this->belongsTo(ProviderProfile::class, 'assigned_provider_id');
    }
}
