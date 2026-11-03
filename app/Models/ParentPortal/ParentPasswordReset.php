<?php

namespace App\Models\ParentPortal;

use Illuminate\Database\Eloquent\Model;

class ParentPasswordReset extends Model
{
    protected $fillable = [
        'phone',
        'otp_hash',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
