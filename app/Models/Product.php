<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];
    public function variants(){
        return $this->hasMany(ProductVariant::class);
    }
    public function prices(){
        return $this->hasMany(ProductVariantPrice::class);
    }


}
