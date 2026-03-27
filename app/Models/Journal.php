<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'public_id',
        'title',
        'journal_author',
        'publish_date',
        'doi',
        'journal_abstract',
        'journal_file',
        'slug',
        'publisher_id',
    ];

    public function publisher(){
        return $this->belongsTo(User::class, 'publisher_id');
    }
}
