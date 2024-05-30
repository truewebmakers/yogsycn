<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;
    protected $primaryKey = '_id';
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'name',
        'description',
        'veg',
        'price',
        'best_seller',
        'product_category_id',
        'image',
        'disable'
    ];

    public function productCategory()
    {
        return $this->belongsTo(product_category::class, 'product_category_id');
    }

    public function productSizes()
    {
        return $this->hasMany(product_size::class, 'product_id');
    }
}
