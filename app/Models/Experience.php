<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $table = 'experiences';

    protected $fillable = [
        'doctor_id',
        'years',
        'description'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
