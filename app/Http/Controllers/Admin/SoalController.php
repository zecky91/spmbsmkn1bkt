<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Soal;
use App\Models\Jurusan;
use Inertia\Inertia;

class SoalController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Soal', [
            'soal' => Soal::with('jurusan')->get(),
            'jurusan' => Jurusan::where('aktif', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $jurusan = Jurusan::findOrFail($request->jurusan_id);
        $isLikert = $jurusan->teknik_penilaian === 'likert';

        $rules = [
            'jurusan_id' => 'required|exists:jurusan,id',
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'opsi_e' => $isLikert ? 'nullable|string' : 'required|string',
            'kunci' => $isLikert ? 'nullable|string|size:1|in:A,B,C,D,E,a,b,c,d,e' : 'required|string|size:1|in:A,B,C,D,E,a,b,c,d,e',
        ];

        $data = $request->validate($rules);

        if ($isLikert) {
            $data['opsi_e'] = $data['opsi_e'] ?? '-';
            $data['kunci'] = strtoupper($data['kunci'] ?? 'A');
        } else {
            $data['kunci'] = strtoupper($data['kunci']);
        }

        Soal::create($data);

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function update(Request $request, Soal $soal)
    {
        $jurusan = Jurusan::findOrFail($request->jurusan_id);
        $isLikert = $jurusan->teknik_penilaian === 'likert';

        $rules = [
            'jurusan_id' => 'required|exists:jurusan,id',
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'opsi_e' => $isLikert ? 'nullable|string' : 'required|string',
            'kunci' => $isLikert ? 'nullable|string|size:1|in:A,B,C,D,E,a,b,c,d,e' : 'required|string|size:1|in:A,B,C,D,E,a,b,c,d,e',
        ];

        $data = $request->validate($rules);

        if ($isLikert) {
            $data['opsi_e'] = $data['opsi_e'] ?? '-';
            $data['kunci'] = strtoupper($data['kunci'] ?? 'A');
        } else {
            $data['kunci'] = strtoupper($data['kunci']);
        }

        $soal->update($data);

        return back()->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Soal $soal)
    {
        $soal->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = $file->getClientOriginalExtension();

        if (in_array(strtolower($extension), ['xls', 'xlsx'])) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } else {
            $content = file_get_contents($path);
            
            // Remove UTF-8 BOM if present
            $content = preg_replace('/^[\xEF\xBB\xBF]+/', '', $content);
            
            // Normalize line endings
            $content = str_replace(["\r\n", "\r"], "\n", trim($content));
            $lines = explode("\n", $content);

            // Detect delimiter based on the first line
            $delimiter = ',';
            if (count($lines) > 0) {
                $firstLine = $lines[0];
                $commaCount = substr_count($firstLine, ',');
                $semiCount = substr_count($firstLine, ';');
                $pipeCount = substr_count($firstLine, '|');
                
                if ($pipeCount >= 7) {
                    $delimiter = '|';
                } elseif ($semiCount >= 7) {
                    $delimiter = ';';
                } elseif ($commaCount >= 7) {
                    $delimiter = ',';
                }
            }

            $rows = array_map(function($line) use ($delimiter) {
                return str_getcsv($line, $delimiter);
            }, $lines);
        }

        // Filter out empty rows or rows that don't have enough columns
        $rows = array_filter($rows, function($row) {
            return count($row) >= 6 && trim($row[0] ?? '') !== '';
        });

        // Re-index array
        $rows = array_values($rows);

        // Check if header exists and skip it
        if (count($rows) > 0 && strtolower(trim($rows[0][0] ?? '')) === 'jurusan') {
            array_shift($rows);
        }

        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $lineNum = $index + 2;

            $jurusanKode = trim($row[0]);
            $pertanyaan = trim($row[1]);
            $opsi_a = trim($row[2]);
            $opsi_b = trim($row[3]);
            $opsi_c = trim($row[4]);
            $opsi_d = trim($row[5]);
            $opsi_e = isset($row[6]) ? trim($row[6]) : '';
            $kunci = isset($row[7]) ? strtoupper(trim($row[7])) : '';

            $jurusan = Jurusan::where('kode', $jurusanKode)->first();

            if (!$jurusan) {
                $errors[] = "Baris {$lineNum}: Jurusan dengan kode '{$jurusanKode}' tidak ditemukan.";
                continue;
            }

            $isLikert = $jurusan->teknik_penilaian === 'likert';

            if (empty($pertanyaan) || empty($opsi_a) || empty($opsi_b) || empty($opsi_c) || empty($opsi_d) || (!$isLikert && (empty($opsi_e) || empty($kunci)))) {
                $errors[] = "Baris {$lineNum}: Kolom tidak boleh kosong.";
                continue;
            }

            if (!$isLikert && !in_array($kunci, ['A', 'B', 'C', 'D', 'E'])) {
                $errors[] = "Baris {$lineNum}: Kunci jawaban harus A, B, C, D, atau E.";
                continue;
            }

            $opsi_e_val = $isLikert ? ($opsi_e ?: '-') : $opsi_e;
            $kunci_val = $isLikert ? ($kunci ?: 'A') : $kunci;

            Soal::create([
                'jurusan_id' => $jurusan->id,
                'pertanyaan' => $pertanyaan,
                'opsi_a' => $opsi_a,
                'opsi_b' => $opsi_b,
                'opsi_c' => $opsi_c,
                'opsi_d' => $opsi_d,
                'opsi_e' => $opsi_e_val,
                'kunci' => $kunci_val,
            ]);

            $imported++;
        }

        if (count($errors) > 0) {
            $errStr = implode(" | ", array_slice($errors, 0, 3));
            if ($imported > 0) {
                return back()->with('warning', "Diimpor: {$imported} soal. Kesalahan: {$errStr}");
            }
            return back()->with('error', "Gagal mengimpor: {$errStr}");
        }

        return back()->with('success', "Berhasil mengimpor {$imported} soal.");
    }
}
