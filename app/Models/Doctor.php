<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty_id',
        'room',
        'appointment_duration'
    ];

    public function getAvailableTimes($date)
    {
        $date = Carbon::parse($date);
        $dayOfWeek = $date->dayOfWeek;

        $schedule = $this->schedules()->where('day_of_week', $dayOfWeek)->first();

        if (!$schedule) {
            return [];
        }

        $bookedTimes = $this->appointments()
            ->whereDate('start_time', $date)
            ->whereIn('status', ['confirmed', 'pending'])
            ->get()
            ->map(function ($appointment) {
                return $appointment->start_time->format('H:i');
            })
            ->toArray();

        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);
        $duration = $this->appointment_duration ?? 30; // minutes
        $times = [];

        $current = $start->copy();
        while ($current->addMinutes($duration)->lte($end)) {
            $timeStr = $current->format('H:i');
            if (!in_array($timeStr, $bookedTimes)) {
                $times[] = $timeStr;
            }
        }

        return $times;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function experience()
    {
        return $this->hasOne(Experience::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function getFullNameAttribute()
    {
        return $this->user->full_name;
    }
}
