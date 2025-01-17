<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PollComment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PollComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(PollComment::class, 'parent_id');
    }
}