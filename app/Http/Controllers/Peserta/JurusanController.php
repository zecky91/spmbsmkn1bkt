<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\HasilUjian;
use Inertia\Inertia;

class JurusanController extends Controller
{
    public function show(Request $request)
    {
        $siswa = Siswa::with(['ruangan', 'jurusan1', 'jurusan2'])->find($request->session()->get('siswa_id'));

        if (!$siswa) {
            return redirect()->route('peserta.login');
        }

        // Check current status
        if ($siswa->status === 'selesai') {
            return redirect()->route('peserta.selesai');
        }

        // Determine which exam is next
        $j1Finished = HasilUjian::where('siswa_id', $siswa->id)
            ->where('jurusan_id', $siswa->jurusan1_id)
            ->whereNotNull('waktu_selesai')
            ->exists();

        $activeJurusanId = null;
        if (!$j1Finished) {
            $activeJurusanId = $siswa->jurusan1_id;
        } elseif ($siswa->jurusan2_id) {
            $j2Finished = HasilUjian::where('siswa_id', $siswa->id)
                ->where('jurusan_id', $siswa->jurusan2_id)
                ->whereNotNull('waktu_selesai')
                ->exists();

            if (!$j2Finished) {
                $activeJurusanId = $siswa->jurusan2_id;
            }
        }

        if (!$activeJurusanId) {
            // Both finished
            $siswa->update(['status' => 'selesai']);
            return redirect()->route('peserta.selesai');
        }

        return Inertia::render('Peserta/PilihJurusan', [
            'siswa' => $siswa,
            'active_jurusan_id' => $activeJurusanId,
            'j1_finished' => $j1Finished,
        ]);
    }

    public function confirm(Request $request)
    {
        $siswa = Siswa::find($request->session()->get('siswa_id'));
        if (!$siswa) {
            return redirect()->route('peserta.login');
        }

        // Determine current active jurusan
        $j1Finished = HasilUjian::where('siswa_id', $siswa->id)
            ->where('jurusan_id', $siswa->jurusan1_id)
            ->whereNotNull('waktu_selesai')
            ->exists();

        $activeJurusanId = null;
        if (!$j1Finished) {
            $activeJurusanId = $siswa->jurusan1_id;
        } elseif ($siswa->jurusan2_id) {
            $j2Finished = HasilUjian::where('siswa_id', $siswa->id)
                ->where('jurusan_id', $siswa->jurusan2_id)
                ->whereNotNull('waktu_selesai')
                ->exists();

            if (!$j2Finished) {
                $activeJurusanId = $siswa->jurusan2_id;
            }
        }

        if (!$activeJurusanId) {
            $siswa->update(['status' => 'selesai']);
            return redirect()->route('peserta.selesai');
        }

        // Update status to proses
        $siswa->update(['status' => 'proses']);

        // Check if HasilUjian exists, if not create it
        $hasil = HasilUjian::firstOrCreate(
            [
                'siswa_id' => $siswa->id,
                'jurusan_id' => $activeJurusanId,
            ],
            [
                'waktu_mulai' => now(),
            ]
        );

        $request->session()->put('current_jurusan_id', $activeJurusanId);

        return redirect()->route('peserta.ujian');
    }
}
