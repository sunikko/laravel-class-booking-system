<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'student_id',
        'class_session_id',
        'booking_date',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }
}
