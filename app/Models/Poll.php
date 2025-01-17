<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    public function comments()
    {
        return $this->hasMany(PollComment::class)->whereNull('parent_id');
    }
    
    public function allComments()
    {
        return $this->hasMany(PollComment::class);
    }
}