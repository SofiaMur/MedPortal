<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

/**
 * @property-read bool $is_admin
 * @property-read bool $is_doctor
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'login',
        'email',
        'password',
        'last_name',
        'first_name',
        'middle_name',
        'gender',
        'birth_date',
        'phone',
        'insurance_number',
        'snils',
        'allergies',
        'chronic_diseases',
        'is_profile_completed'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'is_profile_completed' => 'boolean',
    ];

    protected $appends = ['is_admin', 'is_registrar', 'is_doctor', 'full_name'];

    // Заполнение профиля (is_profile_completed)
    protected static function booted()
    {
        static::updating(function ($user) {
            $requiredFields = [
                'last_name',
                'first_name',
                'middle_name',
                'gender',
                'birth_date',
                'phone',
                'insurance_number',
                'snils'
            ];
            $isCompleted = true;

            foreach ($requiredFields as $field) {
                if (empty($user->$field)) {
                    $isCompleted = false;
                    break;
                }
            }

            $user->is_profile_completed = $isCompleted;
        });
    }

    public function scopeWhereSnils($query, $snils)
    {
        $cleanSnils = preg_replace('/[^0-9]/', '', $snils);

        return $query->get()->filter(function ($user) use ($cleanSnils) {
            $userSnils = preg_replace('/[^0-9]/', '', $user->snils);
            return $userSnils === $cleanSnils;
        });
    }

    // Шифрование и дешифровка данных
    public function setSnilsAttribute($value)
    {
        $this->attributes['snils'] = $value ? Crypt::encrypt($value) : null;
    }

    public function getSnilsAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    public function setInsuranceNumberAttribute($value)
    {
        $this->attributes['insurance_number'] = $value ? Crypt::encrypt($value) : null;
    }

    public function getInsuranceNumberAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $value ? Crypt::encrypt($value) : null;
    }

    public function getPhoneAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    // Полное имя пользователя
    public function getFullNameAttribute()
    {
        return trim($this->last_name . ' ' . $this->first_name . ' ' . $this->middle_name);
    }

    // Получение основных ролей пользователя
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->roles()->where('name', Role::ROLE_ADMIN)->exists();
    }

    public function getIsDoctorAttribute(): bool
    {
        return $this->roles()->where('name', Role::ROLE_DOCTOR)->exists();
    }

    public function getIsRegistrarAttribute(): bool
    {
        return $this->roles()->where('name', Role::ROLE_REGISTRAR)->exists();
    }

    public function appointmentsAsDoctor()
    {
        return $this->hasManyThrough(
            Appointment::class,
            Doctor::class,
            'user_id',
            'doctor_id'
        );
    }

    // Связи между таблицами
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function patientAppointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id')
            ->with(['doctor' => function ($query) {
                $query->with(['user', 'specialty']);
            }]);
    }

    public function registrarAppointments()
    {
        return $this->hasMany(Appointment::class, 'registrar_id');
    }
}
