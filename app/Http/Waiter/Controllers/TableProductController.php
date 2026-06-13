<?php

namespace App\Http\Waiter\Controllers;

use App\Http\Waiter\Requests\AddTableProductRequest;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\RestaurantTableProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TableProductController
{
    protected function ensureScope(Restaurant $restaurant, RestaurantTable $table, ?RestaurantTableProduct $product = null): void
    {
        if ($table->restaurant_id !== $restaurant->id) {
            abort(404);
        }

        if ($product && $product->restaurant_table_id !== $table->id) {
            abort(404);
        }
    }

    public function store(AddTableProductRequest $request, Restaurant $restaurant, RestaurantTable $table): RedirectResponse
    {
        $this->ensureScope($restaurant, $table);

        if (! $table->isOpen()) {
            return redirect()->route('waiter.tables.show', [
                'restaurant' => $restaurant->slug,
                'table' => $table,
            ]);
        }

        $productId = $request->input('product_id');

        $existing = RestaurantTableProduct::where('restaurant_table_id', $table->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->quantity += 1;
            $existing->save();
        } else {
            /** @var Product $product */
            $product = Product::findOrFail($productId);

            RestaurantTableProduct::create([
                'restaurant_table_id' => $table->id,
                'product_id' => $productId,
                'quantity' => 1,
                'unit_price' => $product->price,
            ]);
        }

        return redirect()->route('waiter.tables.show', [
            'restaurant' => $restaurant->slug,
            'table' => $table,
        ]);
    }

    public function update(Request $request, Restaurant $restaurant, RestaurantTable $table, RestaurantTableProduct $product): RedirectResponse
    {
        $this->ensureScope($restaurant, $table, $product);

        if (! $table->isOpen()) {
            return redirect()->route('waiter.tables.show', [
                'restaurant' => $restaurant->slug,
                'table' => $table,
            ]);
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product->quantity = $validated['quantity'];
        $product->save();

        return redirect()->route('waiter.tables.show', [
            'restaurant' => $restaurant->slug,
            'table' => $table,
        ]);
    }

    public function destroy(Restaurant $restaurant, RestaurantTable $table, RestaurantTableProduct $product): RedirectResponse
    {
        $this->ensureScope($restaurant, $table, $product);

        if (! $table->isOpen()) {
            return redirect()->route('waiter.tables.show', [
                'restaurant' => $restaurant->slug,
                'table' => $table,
            ]);
        }

        $product->delete();

        return redirect()->route('waiter.tables.show', [
            'restaurant' => $restaurant->slug,
            'table' => $table,
        ]);
    }
}
