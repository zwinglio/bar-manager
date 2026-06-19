<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Database\Factories\RestaurantTableFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'restaurant_id',
    'waiter_id',
    'number',
    'name',
    'description',
    'person_count',
    'opened_at',
    'closed_at',
    'total',
    'payment_method',
])]
class RestaurantTable extends Model
{
    /** @use HasFactory<RestaurantTableFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'total' => 'decimal:2',
            'person_count' => 'integer',
            'number' => 'integer',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(Waiter::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_restaurant_table')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    public function restaurantTableProducts(): HasMany
    {
        return $this->hasMany(RestaurantTableProduct::class);
    }

    public function getCurrentTotalAttribute(): string
    {
        return number_format(
            $this->products->sum(fn ($p) => $p->pivot->quantity * $p->pivot->unit_price),
            2,
            '.',
            ''
        );
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }

    public function close(PaymentMethod $paymentMethod): void
    {
        $this->closed_at = now();
        $this->total = $this->current_total;
        $this->payment_method = $paymentMethod;
        $this->save();
    }
}
