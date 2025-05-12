<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestedJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'provider_profile_id',
        'is_interested',
        'status',
        'is_selected'
    ];

    // RequestedJob.php

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
