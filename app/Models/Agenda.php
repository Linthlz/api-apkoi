<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $fillable = [
        'public_id',
        'title',
        'slug',
        'tanggal',
        'waktu',
        'category',
        'location',
        'organizer',
        'description',
        'publisher_id',
        'published_at',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function publisher()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }
}
