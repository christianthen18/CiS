<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $attributes = [
        'status_active' => 1,
    ];

    public $timestamps = false;

    public function sales()
    {
        return $this->hasMany(Sales::class, 'customers_id', 'id'); // Corrected to hasMany
    }
}
