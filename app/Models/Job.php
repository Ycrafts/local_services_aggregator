<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'jobs';

    // Fillable attributes for mass assignment
    protected $fillable = [
        'id',
        'user_id',
        'job_type_id',
        'description',
        'proposed_price',
        'status'
    ];

    // Relationship: A job belongs to a customer (user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: A job belongs to a job type
    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    // Relationship: A job can be assigned to a provider profile
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

}
