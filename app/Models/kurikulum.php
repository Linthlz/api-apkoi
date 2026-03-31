<?php

namespace App\Models;

use App\Policies\KurikulumPolicy;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
   protected $fillable = [
    'public_id',
    'title',
    'tanggal',
    'kurikulum_image',
    'kurikulum_file',
    'slug',
    'publisher_id',
    'published_at',
];

public function publisher()
{
    return $this->belongsTo(User::class, 'publisher_id');
}
protected $policies = [
    Kurikulum::class => KurikulumPolicy::class,
];
}
