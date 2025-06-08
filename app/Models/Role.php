<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name', 
    ];

    const ROLE_ADMIN = 'admin';
    const ROLE_REGISTRAR = 'registrar';
    const ROLE_DOCTOR = 'doctor';
    const ROLE_PATIENT = 'patient';

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
}