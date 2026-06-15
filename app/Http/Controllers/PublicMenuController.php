<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\View\View;

class PublicMenuController extends Controller
{
    public function show(Restaurant $restaurant): View
    {
        abort_unless($restaurant->is_active, 404);

        $categories = $restaurant
            ->productCategories()
            ->with([
                'products' => fn ($query) => $query
                    ->where('show_in_menu', true)
                    ->where('available', true)
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->products->isNotEmpty());

        return view('public-menu.show', [
            'restaurant' => $restaurant,
            'categories' => $categories,
        ]);
    }
}
