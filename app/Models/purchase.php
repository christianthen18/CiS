<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class purchase extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'purchase';

    public function updateInventory($cogsMethod, $products)
    {
        foreach ($products as $product) {
            if ($cogsMethod === 'fifo') {
                // Create a new FIFO entry for this purchase
                ProductFifo::create([
                    'product_id' => $product['product_id'],
                    'purchase_date' => now(),
                    'stock' => $product['quantity'],
                    'price' => $product['price'], // Tambahkan field price
                    'purchase_id' => $this->id // Tambahkan purchase_id untuk referensi
                ]);
    
                // Update total product stock
                DB::table('product')
                    ->where('id', $product['product_id'])
                    ->increment('stock', $product['quantity']);
    
            } elseif ($cogsMethod === 'average') {
                // Untuk average, cukup increment stock saja
                DB::table('product')
                    ->where('id', $product['product_id'])
                    ->increment('stock', $product['quantity']);
            }
        }
    }

    public function purchaseDetails()
    {
        return $this->hasMany(purchase_detail::class, 'purchase_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(Payment_Methods::class, 'payment_methods_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, 'suppliers_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
}