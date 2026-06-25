<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilUjian extends Model
{
    protected $table = 'hasil_ujian';
    protected $fillable = ['siswa_id', 'jurusan_id', 'jumlah_jawab', 'benar', 'salah', 'score_ujian', 'nilai_wawancara', 'score_akhir', 'total_soal', 'waktu_mulai', 'waktu_selesai'];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }
}
