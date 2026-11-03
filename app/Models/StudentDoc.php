<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDoc extends Model
{
    protected $fillable = [
        'student_id',
        'doc_name',
        'document_path',
        'created_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
