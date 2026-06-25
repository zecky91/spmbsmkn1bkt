<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurusan extends Model
{
    protected $table = 'jurusan';
    protected $fillable = ['kode', 'nama', 'icon', 'kuota', 'aktif', 'teknik_penilaian'];

    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class, 'jurusan_id');
    }

    public function siswaPilihan1(): HasMany
    {
        return $this->hasMany(Siswa::class, 'jurusan1_id');
    }

    public function siswaPilihan2(): HasMany
    {
        return $this->hasMany(Siswa::class, 'jurusan2_id');
    }
}
