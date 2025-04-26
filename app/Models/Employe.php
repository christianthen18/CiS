<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employe extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'employes'; 
    protected $attributes = [
        'status_active' => 1, // Default aktif saat employee baru dibuat
    ];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    

}
