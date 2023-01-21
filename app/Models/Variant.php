<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];
// relation without any constraints ...works fine

    public function product_variants(){
        return $this->hasMany(ProductVariant::class);
    }


}
