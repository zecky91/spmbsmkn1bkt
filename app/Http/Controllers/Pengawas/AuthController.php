<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengawas;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function show()
    {
        return Inertia::render('Pengawas/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $pengawas = Pengawas::where('username', $request->username)->first();

        if (!$pengawas || !Hash::check($request->password, $pengawas->password)) {
            return back()->with('error', 'Username atau Password salah.');
        }

        $request->session()->put('pengawas_id', $pengawas->id);

        return redirect()->route('pengawas.dashboard')->with('success', 'Login berhasil.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('pengawas_id');
        return redirect()->route('pengawas.login')->with('success', 'Logout berhasil.');
    }
}
