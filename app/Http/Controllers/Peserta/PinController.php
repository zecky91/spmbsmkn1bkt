<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ruangan;
use Inertia\Inertia;

class PinController extends Controller
{
    public function show(Request $request)
    {
        if ($request->session()->has('siswa_id')) {
            return redirect()->route('peserta.pilih-jurusan');
        }
        if ($request->session()->has('ruangan_id')) {
            return redirect()->route('peserta.login');
        }
        return Inertia::render('Peserta/InputPin');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        $ruangan = Ruangan::where('pin', $request->pin)->where('aktif', true)->first();

        if (!$ruangan) {
            return back()->with('error', 'PIN Ruangan tidak valid atau ruangan tidak aktif.');
        }

        $request->session()->put('ruangan_id', $ruangan->id);
        $request->session()->put('pin_entered', true);

        return redirect()->route('peserta.login')->with('success', 'PIN Ruangan berhasil diverifikasi.');
    }
}
