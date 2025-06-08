<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected function checkIsDoctor()
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_doctor) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Доступ запрещен'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Доступ только для врачей');
        }
        
        return null;
    }
}
