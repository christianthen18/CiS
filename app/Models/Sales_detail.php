<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sales_detail extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'sales_detail';

    protected $guarded = ['id'];


    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id', 'id'); // Correct
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id'); // Corrected to belongsTo
    }
}
