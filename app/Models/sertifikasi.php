<?php

namespace App\Models;

use App\Policies\SertifikasiPolicy;
use Illuminate\Database\Eloquent\Model;

class Sertifikasi extends Model
{
   protected $fillable = [
    'public_id',
    'title',
    'sertificate_owner',
    'institution',
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
protected $policies = [
    Sertifikasi::class => SertifikasiPolicy::class,
];
}
