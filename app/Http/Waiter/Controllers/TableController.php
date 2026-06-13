<?php

namespace App\Http\Waiter\Controllers;

use App\Http\Waiter\Requests\OpenTableRequest;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TableController
{
    public function index(Restaurant $restaurant): Response
    {
        $scope = request()->query('scope', 'mine');

        $query = RestaurantTable::with(['restaurantTableProducts.product'])
            ->where('restaurant_id', $restaurant->id);

        if ($scope === 'closed') {
            $query->whereNotNull('closed_at')
                ->where('waiter_id', auth('waiter')->id())
                ->latest('closed_at');
        } else {
            $query->whereNull('closed_at')
                ->latest('opened_at');

            if ($scope === 'mine') {
                $query->where('waiter_id', auth('waiter')->id());
            }
        }

        $tables = $query->get();

        return Inertia::render('Tables/Index', [
            'restaurant' => $restaurant->only('name', 'slug'),
            'waiter' => auth('waiter')->user()?->only('id', 'name'),
            'tables' => $tables->map(fn (RestaurantTable $table) => [
                'id' => $table->id,
                'number' => $table->number,
                'name' => $table->name,
                'person_count' => $table->person_count,
                'opened_at' => $table->opened_at?->toIso8601String(),
                'closed_at' => $table->closed_at?->toIso8601String(),
                'item_count' => $scope === 'closed'
                    ? $table->restaurantTableProducts->sum('quantity')
                    : $table->restaurantTableProducts->sum('quantity'),
                'total' => $scope === 'closed' ? $table->total : $table->current_total,
                'waiter_name' => $table->waiter?->name,
            ]),
            'scope' => $scope,
        ]);
    }

    public function create(Restaurant $restaurant): Response
    {
        return Inertia::render('Tables/Open', [
            'restaurant' => $restaurant->only('name', 'slug'),
        ]);
    }

    public function store(OpenTableRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $table = RestaurantTable::create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => auth('waiter')->id(),
            'number' => $request->input('number'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'person_count' => $request->input('person_count', 1),
            'opened_at' => now(),
        ]);

        return redirect()->route('waiter.tables.show', [
            'restaurant' => $restaurant->slug,
            'table' => $table,
        ]);
    }

    public function show(Restaurant $restaurant, RestaurantTable $table): Response
    {
        $table->load(['restaurantTableProducts.product', 'waiter']);

        $mode = request()->query('mode', 'detail');

        if ($mode === 'menu') {
            $products = $restaurant->products()
                ->where('available', true)
                ->where('show_in_menu', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $categories = $restaurant->productCategories()->orderBy('name')->get();

            return Inertia::render('Tables/Show', [
                'restaurant' => $restaurant->only('name', 'slug'),
                'table' => [
                    'id' => $table->id,
                    'number' => $table->number,
                    'name' => $table->name,
                    'person_count' => $table->person_count,
                    'opened_at' => $table->opened_at?->toIso8601String(),
                    'total' => $table->current_total,
                    'items' => $table->restaurantTableProducts->map(fn ($item) => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => number_format($item->quantity * $item->unit_price, 2, '.', ''),
                    ]),
                    'is_closed' => ! $table->isOpen(),
                ],
                'mode' => 'menu',
                'products' => $products->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'photo_path' => $product->photo_path,
                    'category_id' => $product->product_category_id,
                ]),
                'categories' => $categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]),
            ]);
        }

        return Inertia::render('Tables/Show', [
            'restaurant' => $restaurant->only('name', 'slug'),
            'table' => [
                'id' => $table->id,
                'number' => $table->number,
                'name' => $table->name,
                'person_count' => $table->person_count,
                'opened_at' => $table->opened_at?->toIso8601String(),
                'total' => $table->current_total,
                'items' => $table->restaurantTableProducts->map(fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => number_format($item->quantity * $item->unit_price, 2, '.', ''),
                ]),
                'is_closed' => ! $table->isOpen(),
            ],
            'mode' => 'detail',
        ]);
    }

    public function close(Restaurant $restaurant, RestaurantTable $table): RedirectResponse
    {
        if (! $table->isOpen()) {
            return redirect()->route('waiter.tables.show', [
                'restaurant' => $restaurant->slug,
                'table' => $table,
            ]);
        }

        $table->close();

        return redirect()->route('waiter.tables.index', ['restaurant' => $restaurant->slug]);
    }
}
