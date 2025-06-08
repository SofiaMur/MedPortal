<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Admin/Users/Index', [
            'users' => User::with(['roles', 'doctor'])
                ->when($request->search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,doctor,admin',
            'specialization' => 'required_if:role,doctor|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $role = Role::where('name', $request->role)->first();
            $user->roles()->attach($role);

            if ($request->role === 'doctor') {
                $user->doctor()->create([
                    'specialization' => $request->specialization
                ]);
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'Пользователь успешно зарегистрирован!');
    }

    public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8|confirmed',
        'role' => 'required|in:user,doctor,admin',
        'specialization' => 'required_if:role,doctor|string|max:255|nullable',
    ]);

    DB::transaction(function () use ($request, $user) {
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->password) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        $user->touch();

        $user->roles()->sync([]);
        $role = Role::where('name', $request->role)->first();
        $user->roles()->attach($role);

        if ($request->role === 'doctor') {
            if ($user->doctor) {
                $user->doctor()->update([
                    'specialization' => $request->specialization
                ]);
            } else {
                $user->doctor()->create([
                    'specialization' => $request->specialization
                ]);
            }
        } else {
            if ($user->doctor) {
                $user->doctor()->delete();
            }
        }
    });

    return back()->with('success', 'Данные пользователя успешно обновлены!');
}

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Вы не можете удалить свой собственный аккаунт!');
        }

        DB::transaction(function () use ($user) {
            if ($user->doctor) {
                $user->doctor()->delete();
            }
            
            $user->roles()->sync([]);
            
            $user->delete();
        });

        return back()->with('success', 'Пользователь успешно удален!');
    }
}