<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function show()
    {
        return Inertia::render('Admin/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return back()->with('error', 'Username atau Password salah.');
        }

        $request->session()->put('admin_id', $admin->id);

        return redirect()->route('admin.dashboard')->with('success', 'Login admin berhasil.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_id');
        return redirect()->route('admin.login')->with('success', 'Logout admin berhasil.');
    }
}
