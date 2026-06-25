<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Soal;
use App\Models\HasilUjian;
use App\Models\JawabanUjian;
use Inertia\Inertia;

class UjianController extends Controller
{
    private function getActiveJurusanId(Siswa $siswa)
    {
        $j1Finished = HasilUjian::where('siswa_id', $siswa->id)
            ->where('jurusan_id', $siswa->jurusan1_id)
            ->whereNotNull('waktu_selesai')
            ->exists();

        if (!$j1Finished) {
            return $siswa->jurusan1_id;
        }

        if ($siswa->jurusan2_id) {
            $j2Finished = HasilUjian::where('siswa_id', $siswa->id)
                ->where('jurusan_id', $siswa->jurusan2_id)
                ->whereNotNull('waktu_selesai')
                ->exists();

            if (!$j2Finished) {
                return $siswa->jurusan2_id;
            }
        }

        return null;
    }

    public function show(Request $request)
    {
        $siswa = Siswa::with(['ruangan', 'jurusan1', 'jurusan2'])->find($request->session()->get('siswa_id'));
        if (!$siswa) {
            return redirect()->route('peserta.login');
        }

        $activeJurusanId = $this->getActiveJurusanId($siswa);
        if (!$activeJurusanId) {
            $siswa->update(['status' => 'selesai']);
            return redirect()->route('peserta.selesai');
        }

        $hasil = HasilUjian::firstOrCreate(
            [
                'siswa_id' => $siswa->id,
                'jurusan_id' => $activeJurusanId,
            ],
            [
                'waktu_mulai' => now(),
            ]
        );

        if (!$hasil->waktu_mulai) {
            $hasil->waktu_mulai = now();
            $hasil->save();
        }

        // Calculate secure timer
        $durationSeconds = 15 * 60; // 15 minutes
        $elapsed = now()->timestamp - $hasil->waktu_mulai->timestamp;
        $remaining = $durationSeconds - $elapsed;

        if ($remaining <= 0) {
            $remaining = 0;
            // Mark this exam as finished if time runs out
            $hasil->waktu_selesai = $hasil->waktu_mulai->addSeconds($durationSeconds);
            $this->calculateScore($hasil, $siswa->id, $activeJurusanId);
            
            // Check next
            $nextJurusanId = $this->getActiveJurusanId($siswa);
            if (!$nextJurusanId) {
                $siswa->update(['status' => 'selesai']);
                return redirect()->route('peserta.selesai')->with('warning', 'Waktu ujian telah habis.');
            } else {
                return redirect()->route('peserta.pilih-jurusan')->with('warning', 'Waktu ujian pertama habis. Lanjut ke ujian berikutnya.');
            }
        }

        // Get questions
        $soal = Soal::where('jurusan_id', $activeJurusanId)->get();

        // Get saved answers
        $savedAnswers = JawabanUjian::where('siswa_id', $siswa->id)
            ->where('jurusan_id', $activeJurusanId)
            ->get()
            ->keyBy('soal_id');

        $answers = [];
        $flags = [];
        foreach ($soal as $index => $s) {
            $ans = $savedAnswers->get($s->id);
            if ($ans) {
                if ($ans->jawaban !== null) {
                    $answers[$index] = $ans->jawaban;
                }
                if ($ans->ditandai) {
                    $flags[$index] = true;
                }
            }
        }

        $activeJurusan = \App\Models\Jurusan::find($activeJurusanId);
        
        // Count which exam step this is (1 of 2, or 1 of 1, etc.)
        $totalExams = $siswa->jurusan2_id ? 2 : 1;
        $currentExamStep = ($activeJurusanId === $siswa->jurusan1_id) ? 1 : 2;

        return Inertia::render('Peserta/Ujian', [
            'siswa' => $siswa,
            'jurusan' => $activeJurusan,
            'soal' => $soal,
            'answers' => (object)$answers,
            'flags' => (object)$flags,
            'remaining_seconds' => $remaining,
            'current_exam_step' => $currentExamStep,
            'total_exams' => $totalExams,
        ]);
    }

    public function autoSave(Request $request)
    {
        $siswaId = $request->session()->get('siswa_id');
        $siswa = Siswa::find($siswaId);
        if (!$siswa) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $activeJurusanId = $this->getActiveJurusanId($siswa);
        if (!$activeJurusanId) {
            return response()->json(['error' => 'No active exam'], 400);
        }

        $request->validate([
            'soal_id' => 'required|exists:soal,id',
            'jawaban' => 'nullable|string|in:a,b,c,d,e',
            'ditandai' => 'boolean'
        ]);

        JawabanUjian::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'soal_id' => $request->soal_id,
                'jurusan_id' => $activeJurusanId,
            ],
            [
                'jawaban' => $request->jawaban,
                'ditandai' => $request->ditandai,
            ]
        );

        return response()->json(['status' => 'saved']);
    }

    private function calculateScore(HasilUjian $hasil, $siswaId, $jurusanId)
    {
        $jurusan = \App\Models\Jurusan::find($jurusanId);
        $isLikert = $jurusan && $jurusan->teknik_penilaian === 'likert';

        $soals = Soal::where('jurusan_id', $jurusanId)->get()->keyBy('id');
        $jawabans = JawabanUjian::where('siswa_id', $siswaId)
            ->where('jurusan_id', $jurusanId)
            ->whereNotNull('jawaban')
            ->get();

        $benar = 0;
        $salah = 0;
        $totalPoints = 0;

        foreach ($jawabans as $jawab) {
            $soal = $soals->get($jawab->soal_id);
            if (!$soal) continue;

            if ($isLikert) {
                $jawabanLower = strtolower($jawab->jawaban);
                if ($jawabanLower === 'a') {
                    $totalPoints += 4;
                } elseif ($jawabanLower === 'b') {
                    $totalPoints += 3;
                } elseif ($jawabanLower === 'c') {
                    $totalPoints += 2;
                } elseif ($jawabanLower === 'd') {
                    $totalPoints += 1;
                }
                $benar++; // count of answered questions
            } else {
                if (strtolower($jawab->jawaban) === strtolower($soal->kunci)) {
                    $benar++;
                } else {
                    $salah++;
                }
            }
        }

        $soalCount = $soals->count();

        if ($isLikert) {
            $jawabCount = $benar;
            $scoreUjian = $soalCount > 0 ? ($totalPoints / ($soalCount * 4)) * 100 : 0;
            $salah = $soalCount - $jawabCount;
            $benar = $totalPoints; // Save total points in the 'benar' column
        } else {
            $jawabCount = $benar + $salah;
            $scoreUjian = $soalCount > 0 ? ($benar / $soalCount) * 100 : 0;
        }

        $scoreAkhir = ($scoreUjian * 0.3) + ($hasil->nilai_wawancara ? $hasil->nilai_wawancara * 0.7 : 0);

        $hasil->update([
            'jumlah_jawab' => $jawabCount,
            'benar' => $benar,
            'salah' => $salah,
            'score_ujian' => $scoreUjian,
            'score_akhir' => $scoreAkhir,
            'total_soal' => $soalCount,
            'waktu_selesai' => $hasil->waktu_selesai ?? now(),
        ]);
    }

    public function submit(Request $request)
    {
        $siswa = Siswa::find($request->session()->get('siswa_id'));
        if (!$siswa) {
            return redirect()->route('peserta.login');
        }

        $activeJurusanId = $this->getActiveJurusanId($siswa);
        if (!$activeJurusanId) {
            return redirect()->route('peserta.selesai');
        }

        $hasil = HasilUjian::where('siswa_id', $siswa->id)
            ->where('jurusan_id', $activeJurusanId)
            ->first();

        if ($hasil) {
            $hasil->waktu_selesai = now();
            $this->calculateScore($hasil, $siswa->id, $activeJurusanId);
        }

        // Determine if there is another exam
        $nextJurusanId = $this->getActiveJurusanId($siswa);

        if ($nextJurusanId) {
            return redirect()->route('peserta.pilih-jurusan')->with('success', 'Ujian sebelumnya berhasil dikumpulkan. Silakan lanjutkan ke ujian pilihan kedua.');
        }

        // No more exams
        $siswa->update(['status' => 'selesai']);

        return redirect()->route('peserta.selesai')->with('success', 'Semua ujian berhasil diselesaikan.');
    }
}
