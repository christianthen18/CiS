<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'warehouse';

    public $timestamps = false;

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_has_warehouse', 'warehouse_id', 'product_id')
                    ->withPivot('stock', 'deleted_at');
    }

}
