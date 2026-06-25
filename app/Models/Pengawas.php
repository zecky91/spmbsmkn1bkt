<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengawas extends Authenticatable
{
    protected $table = 'pengawas';
    protected $fillable = ['nama', 'username', 'password', 'ruangan_id'];
    protected $hidden = ['password'];

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }
}
