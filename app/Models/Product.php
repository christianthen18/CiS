<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    protected $table = 'product';
    
    
    // public $timestamps = false;
    use SoftDeletes;

    protected $attributes = [
        'status_active' => 1, // Default aktif saat employee baru dibuat
    ];

    public function productImage()
    {
        return $this->belongsTo(Product_Image::class, 'product_image_id', 'id'); // Kolom foreign key dan primary key
    }

    public function categories()
    {
        return $this->belongsTo(Categories::class, 'categories_id');
    }

    public function suppliers()
    {
        return $this->belongsTo(Suppliers::class, 'suppliers_id');
    }

    public function salesDetails()
    {
        return $this->hasMany(Sales_detail::class, 'product_id', 'id'); // Corrected to hasMany
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_has_warehouse', 'product_id', 'warehouse_id')
                    ->withPivot('stock', 'deleted_at');
    }
    
    public function returns()
    {
        return $this->hasMany(Retur::class, 'product_id');
    }
}
