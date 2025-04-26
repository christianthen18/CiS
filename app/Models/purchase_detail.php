<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class purchase_detail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'purchase_detail'; 

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id'); // Each purchase detail belongs to one purchase
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id'); // Each purchase detail belongs to one product
    }
}
