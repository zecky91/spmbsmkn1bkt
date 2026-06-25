<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ruangan
        Schema::create('ruangan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('pin', 4);
            $table->integer('kapasitas');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 2. Jurusan
        Schema::create('jurusan', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('icon')->nullable();
            $table->integer('kuota');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 3. Siswa
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 10)->unique();
            $table->string('nik', 16);
            $table->string('nama');
            $table->date('tanggal_lahir');
            $table->string('asal_sekolah');
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangan')->onDelete('cascade');
            $table->foreignId('jurusan1_id')->constrained('jurusan')->onDelete('cascade');
            $table->foreignId('jurusan2_id')->nullable()->constrained('jurusan')->onDelete('cascade');
            $table->string('afirmasi')->nullable();
            $table->string('prestasi')->nullable();
            $table->enum('status', ['belum_login', 'login', 'proses', 'selesai', 'macet'])->default('belum_login');
            $table->timestamps();
        });

        // 4. Soal
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id')->constrained('jurusan')->onDelete('cascade');
            $table->text('pertanyaan');
            $table->text('opsi_a');
            $table->text('opsi_b');
            $table->text('opsi_c');
            $table->text('opsi_d');
            $table->text('opsi_e');
            $table->char('kunci', 1);
            $table->timestamps();
        });

        // 5. Pengawas
        Schema::create('pengawas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->foreignId('ruangan_id')->constrained('ruangan')->onDelete('cascade');
            $table->timestamps();
        });

        // 6. Admin
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();
        });

        // 7. Jawaban Ujian
        Schema::create('jawaban_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('soal_id')->constrained('soal')->onDelete('cascade');
            $table->foreignId('jurusan_id')->constrained('jurusan')->onDelete('cascade');
            $table->char('jawaban', 1)->nullable();
            $table->boolean('ditandai')->default(false); // Ragu-ragu
            $table->timestamps();
        });

        // 8. Hasil Ujian
        Schema::create('hasil_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('jurusan_id')->constrained('jurusan')->onDelete('cascade');
            $table->integer('jumlah_jawab')->default(0);
            $table->integer('benar')->default(0);
            $table->integer('salah')->default(0);
            $table->float('score_ujian')->default(0);
            $table->float('nilai_wawancara')->nullable();
            $table->float('score_akhir')->default(0);
            $table->integer('total_soal')->default(0);
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_ujian');
        Schema::dropIfExists('jawaban_ujian');
        Schema::dropIfExists('admin');
        Schema::dropIfExists('pengawas');
        Schema::dropIfExists('soal');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('jurusan');
        Schema::dropIfExists('ruangan');
    }
};
