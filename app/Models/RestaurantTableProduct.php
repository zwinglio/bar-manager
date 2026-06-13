<?php

namespace App\Models;

use Database\Factories\RestaurantTableProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RestaurantTableProduct extends Pivot
{
    /** @use HasFactory<RestaurantTableProductFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'product_restaurant_table';

    protected $fillable = [
        'restaurant_table_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
