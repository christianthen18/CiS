<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingHistory extends Model
{
    use HasFactory;
    
    protected $table = 'shipping_history';
    
    protected $fillable = [
        'sales_id',
        'sales_detail_id',
        'product_id',
        'quantity_shipped',
        'shipped_at'
    ];
    
    protected $casts = [
        'shipped_at' => 'datetime',
    ];
    
    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }
    
    public function salesDetail()
    {
        return $this->belongsTo(Sales_detail::class, 'sales_detail_id');
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}