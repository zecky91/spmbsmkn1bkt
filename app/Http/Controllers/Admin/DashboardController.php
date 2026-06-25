<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function wawancaraPage(Request $request)
    {
        $allowedUsers = ['ahmad_zaki', 'yulia_sandra', 'mardayoni12'];
        $admin = \App\Models\Admin::find($request->session()->get('admin_id'));
        
        if (!$admin || !in_array($admin->username, $allowedUsers)) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $siswa = Siswa::with(['ruangan', 'jurusan1', 'jurusan2', 'hasilUjian'])->get();
        $jurusan = \App\Models\Jurusan::all();

        return Inertia::render('Admin/Wawancara', [
            'siswa' => $siswa,
            'jurusan' => $jurusan
        ]);
    }

    public function rangkingPage(Request $request)
    {
        $adminId = $request->session()->get('admin_id');
        $admin = \App\Models\Admin::find($adminId);
        $allowedUsers = ['ahmad_zaki', 'yulia_sandra', 'mardayoni12'];

        if (!$admin || !in_array($admin->username, $allowedUsers)) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman Rangking.');
        }

        $siswa = Siswa::with(['ruangan', 'jurusan1', 'jurusan2', 'hasilUjian'])->get();
        $jurusan = \App\Models\Jurusan::all();

        return Inertia::render('Admin/Rangking', [
            'siswa' => $siswa,
            'jurusan' => $jurusan
        ]);
    }

    public function updateNilaiRangking(Request $request, Siswa $siswa)
    {
        $adminId = $request->session()->get('admin_id');
        $admin = \App\Models\Admin::find($adminId);
        $allowedUsers = ['ahmad_zaki', 'yulia_sandra', 'mardayoni12'];

        if (!$admin || !in_array($admin->username, $allowedUsers)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'jurusan_id' => 'required|exists:jurusan,id',
            'score_ujian' => 'nullable|numeric|min:0|max:100',
            'nilai_wawancara' => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $jurusanId = $request->jurusan_id;
        
        $hasil = HasilUjian::firstOrNew([
            'siswa_id' => $siswa->id,
            'jurusan_id' => $jurusanId
        ]);

        if ($request->has('score_ujian')) {
            $hasil->score_ujian = $request->score_ujian;
        }
        
        if ($request->has('nilai_wawancara')) {
            $hasil->nilai_wawancara = $request->nilai_wawancara;
        }
        
        if ($request->has('keterangan')) {
            $hasil->keterangan = $request->keterangan;
        }

        // Hitung ulang nilai akhir jika ada nilai
        if ($hasil->score_ujian !== null || $hasil->nilai_wawancara !== null) {
            $o = $hasil->score_ujian ?? 0;
            $w = $hasil->nilai_wawancara ?? 0;
            $hasil->score_akhir = ($o * 0.3) + ($w * 0.7);
        }

        $hasil->save();

        return response()->json([
            'message' => 'Nilai dan keterangan berhasil diperbarui!',
            'hasil' => $hasil
        ]);
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
        $adminId = $request->session()->get('admin_id');
        $admin = \App\Models\Admin::select('id', 'username')->find($adminId);

        if (!$admin || !in_array($admin->username, $allowedUsers)) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        $request->validate([
            'wawancara'   => 'required|array',
            'wawancara.*' => 'nullable|numeric|min:0|max:100',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $siswa) {
            foreach ($request->wawancara as $jurusanId => $nilai) {
                if ($nilai === null || $nilai === '') continue;

                $hasil = HasilUjian::firstOrCreate(
                    ['siswa_id' => $siswa->id, 'jurusan_id' => $jurusanId]
                );

                $scoreAkhir = (($hasil->score_ujian ?? 0) * 0.3) + ($nilai * 0.7);

                $hasil->update([
                    'nilai_wawancara' => $nilai,
                    'score_akhir'     => $scoreAkhir,
                ]);
            }
        });

        // Return JSON ringan, tidak reload seluruh halaman
        return response()->json(['message' => "Nilai wawancara {$siswa->nama} berhasil disimpan."]);
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
