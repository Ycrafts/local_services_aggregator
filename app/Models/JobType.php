<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'baseline_price'
    ];

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function providerProfiles()
    {
        return $this->belongsToMany(ProviderProfile::class, 'provider_profile_job_type');
    }
}
