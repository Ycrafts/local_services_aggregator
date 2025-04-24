<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderProfile extends Model
{
    protected $fillable = ['user_id', 'skills', 'experience_years', 'rating', 'bio', 'location'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
