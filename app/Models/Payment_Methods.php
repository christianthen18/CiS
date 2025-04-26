<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment_Methods extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'payment_methods';


    public function Sales()
    {
        return $this->belongsTo(Sales_detail::class, 'payment_methods_id', 'id'); // Kolom foreign key dan primary key
    }
}
