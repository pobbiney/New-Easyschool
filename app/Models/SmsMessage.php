<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{

    protected $fillable = [
        'audience',
        'school_class_id',
        'target_type',
        'target_id',
        'audience_label',
        'message',
        'recipient_count',
        'sent_count',
        'skipped_count',
        'status',
        'response',
        'created_by',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
