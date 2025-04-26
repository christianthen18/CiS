<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product_Image extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'product_image';
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(Product::class, 'product_image_id', 'id'); // Kolom foreign key dan primary key
    }

}
