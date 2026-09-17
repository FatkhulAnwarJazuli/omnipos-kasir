<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'buy_price',
        'sell_price',
        'stock',
    ];

    // Tambahkan fungsi relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}