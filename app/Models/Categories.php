<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categories extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(Product::class, 'categories_id', 'id'); // Kolom foreign key dan primary key
    }

}
