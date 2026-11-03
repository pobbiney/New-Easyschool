<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccessLink extends Model
{
    protected $table = 'user_access_links';

    public $incrementing = false;

    protected $casts = [
        'user_id' => 'int',
        'link_id' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'link_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function link()
    {
        return $this->belongsTo(UserLink::class, 'link_id', 'link_id');
    }
}
