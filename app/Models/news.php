<?php

namespace App\Models;

use App\Policies\NewsPolicy;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'public_id',
        'title',
        'tanggal',
        'news_image',
        'content',
        'slug',
        'publisher_id',
        'published_at',
        'category'
    ];
    public function publisher()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }
    protected $policies = [
    News::class => NewsPolicy::class,
    ];
}
