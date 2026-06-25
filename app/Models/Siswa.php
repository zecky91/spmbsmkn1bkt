<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = [
        'nisn', 'nik', 'nama', 'tanggal_lahir', 'asal_sekolah',
        'ruangan_id', 'jurusan1_id', 'jurusan2_id', 'afirmasi', 'prestasi', 'status',
        'created_by'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function jurusan1(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan1_id');
    }

    public function jurusan2(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan2_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function jawabanUjian(): HasMany
    {
        return $this->hasMany(JawabanUjian::class, 'siswa_id');
    }

    public function hasilUjian(): HasMany
    {
        return $this->hasMany(HasilUjian::class, 'siswa_id');
    }
}
