<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sales extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    public function updateInventory($cogsMethod, $products)
    {
        foreach ($products as $product) {
            $productId = $product['product_id'];
            $quantity  = $product['quantity'];

            // Update main product stock
            if ($cogsMethod === 'fifo') {
                // Process FIFO inventory
                $productStocks = ProductFifo::where('product_id', $productId)
                    ->orderBy('purchase_date', 'asc')
                    ->get();

                $totalFifoStock = $productStocks->sum('stock');

                if ($totalFifoStock < $quantity) {
                    throw new \Exception("Insufficient FIFO stock for product: " . $productId);
                }

                $remainingQty = $quantity;
                foreach ($productStocks as $stock) {
                    if ($remainingQty <= 0) {
                        break;
                    }

                    $decrementQty = min($remainingQty, $stock->stock);

                    if ($decrementQty > 0) {
                        $stock->decrement('stock', $decrementQty);
                        $remainingQty -= $decrementQty;
                    }
                }

                // Update main product table stock
                DB::table('product')
                    ->where('id', $productId)
                    ->decrement('stock', $quantity);
            } elseif ($cogsMethod === 'average') {
                // Only update the main product table for average costing
                DB::table('product')
                    ->where('id', $productId)
                    ->decrement('stock', $quantity);
            }

            // DIRECTLY update product_has_warehouse stock - separated from COGS method logic
            try {
                // Get warehouses with stock for this product
                $warehouseStocks = DB::table('product_has_warehouse')
                    ->where('product_id', $productId)
                    ->where('stock', '>', 0)
                    ->orderBy('warehouse_id')
                    ->get();

                // Debug log to check what warehouses are found
                \Log::info("Found " . count($warehouseStocks) . " warehouses with stock for product $productId");

                $remainingToDecrement = $quantity;

                foreach ($warehouseStocks as $warehouseStock) {
                    if ($remainingToDecrement <= 0) {
                        break;
                    }

                    $warehouseDecrementQty = min($remainingToDecrement, $warehouseStock->stock);

                    // Debug log the decrement action
                    \Log::info("Decrementing product $productId in warehouse {$warehouseStock->warehouse_id} by $warehouseDecrementQty units");

                    $affected = DB::table('product_has_warehouse')
                        ->where('product_id', $productId)
                        ->where('warehouse_id', $warehouseStock->warehouse_id)
                        ->decrement('stock', $warehouseDecrementQty);

                    // Debug log to check if rows were affected
                    \Log::info("Affected rows: $affected");

                    $remainingToDecrement -= $warehouseDecrementQty;
                }

                // Log if we couldn't decrement all the requested quantity
                if ($remainingToDecrement > 0) {
                    \Log::warning("Could not decrement all requested quantity from warehouses. Remaining: $remainingToDecrement");
                }
            } catch (\Exception $e) {
                // Log the exception but don't throw it to avoid interrupting the transaction
                \Log::error("Error updating warehouse stock: " . $e->getMessage());
                \Log::error($e->getTraceAsString());
            }
        }
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employes_id', 'id'); // Corrected to belongsTo
    }

    public function salesDetail()
    {
        return $this->hasMany(Sales_detail::class, 'sales_id', 'id'); // Ensure this is correct
    }

    public function paymentMethod()
    {
        return $this->belongsTo(Payment_Methods::class, 'payment_methods_id', 'id'); // Corrected to belongsTo
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customers_id', 'id'); // Corrected to belongsTo
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sales) {
            $lastNota = self::latest('created_at')->first();

            $nextNotaNumber = $lastNota ? (int) substr($lastNota->noNota, 3) + 1 : 1;

            $sales->noNota = 'INV' . str_pad($nextNotaNumber, 4, '0', STR_PAD_LEFT);
            $sales->date   = Carbon::now();
        });
    }
}
