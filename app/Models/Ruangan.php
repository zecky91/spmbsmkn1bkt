<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    protected $table = 'ruangan';
    protected $fillable = ['nama', 'pin', 'kapasitas', 'aktif'];

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'ruangan_id');
    }

    public function pengawas(): HasMany
    {
        return $this->hasMany(Pengawas::class, 'ruangan_id');
    }
}
