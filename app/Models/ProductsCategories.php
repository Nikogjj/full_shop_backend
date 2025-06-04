<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsCategories extends Model
{
    protected $table = "_products_categories";

    protected $fillable = [
        'product_id',
        'category_id'
    ];
}
