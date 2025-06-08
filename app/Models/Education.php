<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    protected $table = 'educations';

    protected $fillable = [
        'doctor_id', 
        'institution', 
        'ended_year'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
