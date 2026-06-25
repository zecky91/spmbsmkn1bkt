<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Ruangan;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function show(Request $request)
    {
        if ($request->session()->has('siswa_id')) {
            return redirect()->route('peserta.pilih-jurusan');
        }

        $ruangan = Ruangan::find($request->session()->get('ruangan_id'));

        return Inertia::render('Peserta/Login', [
            'ruangan' => $ruangan
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|size:10',
            'tanggal_lahir' => 'required|date',
        ]);

        $ruanganId = $request->session()->get('ruangan_id');

        // Cari siswa hanya berdasarkan NISN dan tanggal lahir
        $siswa = Siswa::where('nisn', $request->nisn)
            ->where('tanggal_lahir', $request->tanggal_lahir)
            ->first();

        if (!$siswa) {
            return back()->with('error', 'NISN atau Tanggal Lahir salah. Silakan periksa kembali.');
        }

        if ($siswa->status === 'selesai') {
            return back()->with('error', 'Anda sudah menyelesaikan semua ujian.');
        }

        if ($siswa->status === 'macet') {
            return back()->with('error', 'Status Anda MACET. Silakan lapor ke Pengawas untuk me-reset status Anda.');
        }

        // Otomatis isi/update ruangan_id sesuai PIN ruangan yang dimasukkan
        $siswa->update([
            'status'      => 'login',
            'ruangan_id'  => $ruanganId,
        ]);

        $request->session()->put('siswa_id', $siswa->id);

        return redirect()->route('peserta.pilih-jurusan')->with('success', 'Login berhasil.');
    }

    public function logout(Request $request)
    {
        $siswaId = $request->session()->get('siswa_id');
        if ($siswaId) {
            $siswa = Siswa::find($siswaId);
            if ($siswa && ($siswa->status === 'login' || $siswa->status === 'proses')) {
                $siswa->update(['status' => 'belum_login']);
            }
        }

        $request->session()->forget('siswa_id');
        return redirect()->route('peserta.login')->with('success', 'Logout berhasil.');
    }
}
