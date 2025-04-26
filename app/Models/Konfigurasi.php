<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konfigurasi extends Model
{
    use HasFactory;
    protected $table = 'konfigurasi';

    public function details()
    {
        return $this->hasMany(detailKonfigurasi::class, 'konfigurasi_id');
    }
}
