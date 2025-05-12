<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'provider_profile_id',
        'customer_id',
        'rating',
        'comment',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
