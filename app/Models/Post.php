<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_draft',
        'published_at',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_draft', false)
            ->whereNotNull('published_at');
    }

    public function isPublished()
    {
        return ! $this->is_draft && ! is_null($this->published_at);
    }
}
