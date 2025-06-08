<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

   public function rules()
{
    return [
        'doctor_id' => 'required|exists:doctors,id',
        'start_time' => 'required|date_format:Y-m-d\TH:i:s',
        'reason' => 'nullable|string|max:1000',
    ];
}
}