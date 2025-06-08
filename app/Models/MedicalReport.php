<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'diagnosis',
        'recommendations',
        'prescription'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
