<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sertifikasi extends Model
{
   protected $fillable = [
    'public_id',
    'title',
    'sertificate_owner',
    'sertificate_code',
    'sertificate_image',
    'sertificate_file',
    'slug',
    'publisher_id',
    'tanggal',
    'published_at',
];

public function publisher()
{
    return $this->belongsTo(User::class, 'publisher_id');
}
}
