<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Ruangan;
use App\Models\JawabanUjian;
use App\Models\HasilUjian;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private function getGlobalData()
    {
        $siswa = Siswa::with(['ruangan', 'jurusan1', 'jurusan2', 'hasilUjian'])->get();
        $ruangan = Ruangan::all();

        $stats = [
            'total' => $siswa->count(),
            'login' => $siswa->whereIn('status', ['login', 'proses', 'selesai', 'macet'])->count(),
            'proses' => $siswa->where('status', 'proses')->count(),
            'selesai' => $siswa->where('status', 'selesai')->count(),
            'macet' => $siswa->where('status', 'macet')->count(),
        ];

        return [
            'siswa' => $siswa,
            'ruangan' => $ruangan,
            'stats' => $stats
        ];
    }

    public function index()
    {
        return Inertia::render('Admin/Dashboard', $this->getGlobalData());
    }

    public function poll()
    {
        return response()->json($this->getGlobalData());
    }

    public function reset(Request $request, Siswa $siswa)
    {
        // Reset status to belum_login so they can log back in, but keep answers
        $siswa->update(['status' => 'belum_login']);
        return back()->with('success', "Status ujian {$siswa->nama} berhasil direset.");
    }

    public function gugur(Request $request, Siswa $siswa)
    {
        // Disqualify: reset status and wipe all answers/results
        $siswa->update(['status' => 'belum_login']);
        
        JawabanUjian::where('siswa_id', $siswa->id)->delete();
        HasilUjian::where('siswa_id', $siswa->id)->delete();

        return back()->with('success', "Peserta {$siswa->nama} berhasil digugurkan (jawaban dihapus).");
    }

    public function saveWawancara(Request $request, Siswa $siswa)
    {
        // Hanya admin tertentu yang boleh input nilai wawancara
        $allowedUsers = ['ahmad_zaki', 'yulia_sandra', 'mardayoni12'];
        $admin = \App\Models\Admin::find($request->session()->get('admin_id'));
        if (!$admin || !in_array($admin->username, $allowedUsers)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk input nilai wawancara.');
        }

        $request->validate([
            'wawancara' => 'required|array',
            'wawancara.*' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach ($request->wawancara as $jurusanId => $nilai) {
            if ($nilai !== null) {
                $hasil = HasilUjian::firstOrCreate(
                    ['siswa_id' => $siswa->id, 'jurusan_id' => $jurusanId]
                );
                
                $scoreUjian = $hasil->score_ujian ?? 0;
                $scoreAkhir = ($scoreUjian * 0.3) + ($nilai * 0.7);

                $hasil->update([
                    'nilai_wawancara' => $nilai,
                    'score_akhir' => $scoreAkhir
                ]);
            }
        }

        return back()->with('success', "Nilai wawancara {$siswa->nama} berhasil disimpan.");
    }

    public function export()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=hasil_ppdb_exam_" . now()->format('Y-m-d_H-i-s') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $siswa = Siswa::with(['ruangan', 'jurusan1', 'jurusan2', 'hasilUjian.jurusan'])->get();

        $callback = function() use($siswa) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NISN', 'Nama', 'Ruangan', 'Pilihan 1', 'Pilihan 2', 'Status', 'Hasil Ujian', 'Score Ujian', 'Wawancara', 'Score Akhir']);

            foreach ($siswa as $s) {
                $hasilTexts = [];
                $ujianTexts = [];
                $wawancaraTexts = [];
                $akhirTexts = [];
                foreach ($s->hasilUjian as $h) {
                    $hasilTexts[] = $h->jurusan->kode . ": " . $h->jumlah_jawab . "/" . $h->total_soal;
                    $ujianTexts[] = $h->jurusan->kode . ": " . round($h->score_ujian, 2);
                    $wawancaraTexts[] = $h->jurusan->kode . ": " . ($h->nilai_wawancara !== null ? round($h->nilai_wawancara, 2) : '-');
                    $akhirTexts[] = $h->jurusan->kode . ": " . round($h->score_akhir, 2);
                }
                fputcsv($file, [
                    $s->nisn,
                    $s->nama,
                    $s->ruangan->nama ?? '-',
                    $s->jurusan1->kode ?? '-',
                    $s->jurusan2->kode ?? '-',
                    $s->status,
                    implode(' | ', $hasilTexts),
                    implode(' | ', $ujianTexts),
                    implode(' | ', $wawancaraTexts),
                    implode(' | ', $akhirTexts)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
