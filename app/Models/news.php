<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class news extends Model
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
];
public function publisher()
{
    return $this->belongsTo(User::class, 'publisher_id');
}
}
