<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detailKonfigurasi extends Model
{
    use HasFactory;
    protected $table = 'detailkonfigurasi';
    public $timestamps = false;

    public function konfigurasi()
    {
        return $this->belongsTo(Konfigurasi::class, 'konfigurasi_id');
    }

}
