<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/BackupRestore');
    }

    /**
     * Tabel-tabel yang dikecualikan dari backup (data sesi/cache sementara)
     */
    private $excludedTables = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'migrations'];

    public function backup(Request $request)
    {
        try {
            $database = config('database.connections.mysql.database');
            $tables = DB::select('SHOW TABLES');
            $key = 'Tables_in_' . $database;

            $sql = "-- PPDB Exam Database Backup\n";
            $sql .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: {$database}\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$key;

                // Lewati tabel yang dikecualikan
                if (in_array($tableName, $this->excludedTables)) {
                    continue;
                }

                // Ambil CREATE TABLE statement
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sql .= "-- Struktur tabel: {$tableName}\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

                // Ambil semua data
                $rows = DB::select("SELECT * FROM `{$tableName}`");

                if (count($rows) > 0) {
                    $sql .= "-- Data tabel: {$tableName} (" . count($rows) . " baris)\n";

                    foreach ($rows as $row) {
                        $values = [];
                        foreach ((array)$row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $columns = array_keys((array)$row);
                        $sql .= "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $fileName = 'backup_ppdb_' . date('Y-m-d_H-i-s') . '.sql';
            $tempPath = storage_path('app/' . $fileName);

            File::put($tempPath, $sql);

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
