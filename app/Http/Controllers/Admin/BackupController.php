<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Ifsnop\Mysqldump as IMysqldump;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/BackupRestore');
    }

    public function backup(Request $request)
    {
        try {
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST', '127.0.0.1');
            $port = env('DB_PORT', '3306');

            // Kita kecualikan tabel yang berisi data sesi sementara agar tidak me-logout user
            $dumpSettings = array(
                'exclude-tables' => ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'],
                'compress' => IMysqldump\Mysqldump::NONE,
            );

            $dump = new IMysqldump\Mysqldump(
                "mysql:host={$host};port={$port};dbname={$database}",
                $username,
                $password,
                $dumpSettings
            );

            $fileName = 'backup_ppdb_' . date('Y-m-d_H-i-s') . '.sql';
            $tempPath = storage_path('app/' . $fileName);

            $dump->start($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file',
        ], [
            'backup_file.required' => 'Silakan unggah file SQL.',
        ]);

        try {
            $file = $request->file('backup_file');
            
            // Validasi manual ekstensi
            if ($file->getClientOriginalExtension() !== 'sql') {
                return back()->with('error', 'File harus berekstensi .sql');
            }

            $sqlContent = File::get($file->getRealPath());

            // Nonaktifkan Foreign Key Check sementara saat me-restore
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Jalankan seluruh statement SQL
            DB::unprepared($sqlContent);

            // Aktifkan kembali Foreign Key Check
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('success', 'Database berhasil di-restore!');
        } catch (\Exception $e) {
            // Pastikan foreign key nyala lagi jika terjadi error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Gagal me-restore database: ' . $e->getMessage());
        }
    }
}
