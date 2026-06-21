<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'title',
        'issuer',
        'description',
        'date',
        'expires_date',
        'credential_url',
        'image',
    ];

    protected $casts = [
        'date' => 'date',
        'expires_date' => 'date',
    ];
}
