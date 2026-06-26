<?php

use Illuminate\Support\Facades\Route;

// Import Controllers
use App\Http\Controllers\Peserta\PinController;
use App\Http\Controllers\Peserta\AuthController as PesertaAuthController;
use App\Http\Controllers\Peserta\JurusanController as PesertaJurusanController;
use App\Http\Controllers\Peserta\UjianController as PesertaUjianController;
use App\Http\Controllers\Peserta\SelesaiController as PesertaSelesaiController;

use App\Http\Controllers\Pengawas\AuthController as PengawasAuthController;
use App\Http\Controllers\Pengawas\DashboardController as PengawasDashboardController;

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SiswaController as AdminSiswaController;
use App\Http\Controllers\Admin\JurusanController as AdminJurusanController;
use App\Http\Controllers\Admin\SoalController as AdminSoalController;
use App\Http\Controllers\Admin\RuanganController as AdminRuanganController;
use App\Http\Controllers\Admin\PengawasController as AdminPengawasController;
use App\Http\Controllers\Admin\BackupController as AdminBackupController;

// === Peserta (Student) Flow ===
// 1. Enter Room PIN
Route::get('/', [PinController::class, 'show'])->name('pin.show');
Route::post('/', [PinController::class, 'verify'])->name('pin.verify');

// 2. Login Student (only if PIN entered)
Route::middleware('pin')->group(function () {
    Route::get('/login', [PesertaAuthController::class, 'show'])->name('peserta.login');
    Route::post('/login', [PesertaAuthController::class, 'login'])->name('peserta.login.post');
    Route::post('/logout-peserta', [PesertaAuthController::class, 'logout'])->name('peserta.logout');
});

// 3. Exam Flow (must be authenticated as student)
Route::middleware(['pin', 'peserta'])->group(function () {
    Route::get('/pilih-jurusan', [PesertaJurusanController::class, 'show'])->name('peserta.pilih-jurusan');
    Route::post('/pilih-jurusan', [PesertaJurusanController::class, 'confirm'])->name('peserta.pilih-jurusan.confirm');
    
    Route::get('/ujian', [PesertaUjianController::class, 'show'])->name('peserta.ujian');
    Route::post('/ujian/save', [PesertaUjianController::class, 'autoSave'])->name('peserta.ujian.save');
    Route::post('/ujian/submit', [PesertaUjianController::class, 'submit'])->name('peserta.ujian.submit');
    
    Route::get('/selesai', [PesertaSelesaiController::class, 'show'])->name('peserta.selesai');
});

// === Pengawas (Supervisor) Flow ===
Route::prefix('pengawas')->name('pengawas.')->group(function () {
    Route::get('/login', [PengawasAuthController::class, 'show'])->name('login');
    Route::post('/login', [PengawasAuthController::class, 'login'])->name('login.post');
    
    Route::middleware('pengawas')->group(function () {
        Route::get('/dashboard', [PengawasDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/poll', [PengawasDashboardController::class, 'poll'])->name('dashboard.poll');
        Route::post('/reset/{siswa}', [PengawasDashboardController::class, 'reset'])->name('siswa.reset');
        Route::post('/logout', [PengawasAuthController::class, 'logout'])->name('logout');
    });
});

// === Admin Flow ===
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'show'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/poll', [AdminDashboardController::class, 'poll'])->name('dashboard.poll');
        Route::get('/wawancara', [AdminDashboardController::class, 'wawancaraPage'])->name('wawancara.index');
        Route::get('/rangking', [AdminDashboardController::class, 'rangkingPage'])->name('rangking.index');
        Route::post('/rangking/update/{siswa}', [AdminDashboardController::class, 'updateNilaiRangking'])->name('rangking.update');
        Route::post('/reset/{siswa}', [AdminDashboardController::class, 'reset'])->name('siswa.reset');
        Route::post('/gugur/{siswa}', [AdminDashboardController::class, 'gugur'])->name('siswa.gugur');
        Route::post('/wawancara/{siswa}', [AdminDashboardController::class, 'saveWawancara'])->name('siswa.wawancara');
        Route::get('/export', [AdminDashboardController::class, 'export'])->name('export');
        
        // Backup & Restore
        Route::get('/backup-restore', [AdminBackupController::class, 'index'])->name('backup.index');
        Route::get('/backup-restore/download', [AdminBackupController::class, 'backup'])->name('backup.download');
        Route::post('/backup-restore/upload', [AdminBackupController::class, 'restore'])->name('backup.upload');
        
        // CRUD resources
        Route::resource('siswa', AdminSiswaController::class);
        Route::resource('jurusan', AdminJurusanController::class);
        
        Route::resource('soal', AdminSoalController::class);
        Route::post('soal/import', [AdminSoalController::class, 'import'])->name('soal.import');
        
        Route::resource('ruangan', AdminRuanganController::class);
        Route::resource('pengawas', AdminPengawasController::class)->parameters(['pengawas' => 'pengawas']);
        
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});

