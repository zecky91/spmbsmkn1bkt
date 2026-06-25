<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\HasilUjian;
use Inertia\Inertia;

class SelesaiController extends Controller
{
    public function show(Request $request)
    {
        $siswa = Siswa::with(['ruangan', 'jurusan1', 'jurusan2'])->find($request->session()->get('siswa_id'));
        if (!$siswa) {
            return redirect()->route('peserta.login');
        }

        // Fetch exam results
        $results = HasilUjian::with('jurusan')
            ->where('siswa_id', $siswa->id)
            ->get();

        // Check if student is actually done
        if ($siswa->status !== 'selesai') {
            // Check if they still have unfinished exams
            $j1Finished = HasilUjian::where('siswa_id', $siswa->id)
                ->where('jurusan_id', $siswa->jurusan1_id)
                ->whereNotNull('waktu_selesai')
                ->exists();

            if (!$j1Finished) {
                return redirect()->route('peserta.pilih-jurusan');
            }

            if ($siswa->jurusan2_id) {
                $j2Finished = HasilUjian::where('siswa_id', $siswa->id)
                    ->where('jurusan_id', $siswa->jurusan2_id)
                    ->whereNotNull('waktu_selesai')
                    ->exists();

                if (!$j2Finished) {
                    return redirect()->route('peserta.pilih-jurusan');
                }
            }

            // If actually finished but status not updated, update it
            $siswa->update(['status' => 'selesai']);
        }

        return Inertia::render('Peserta/Selesai', [
            'siswa' => $siswa,
            'results' => $results,
        ]);
    }
}
