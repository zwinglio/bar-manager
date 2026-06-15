<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function downloadQr(Restaurant $restaurant): StreamedResponse
    {
        abort_unless(
            Auth::user()?->restaurant_id === $restaurant->id,
            403,
        );

        $url = route('menu.public', ['restaurant' => $restaurant->slug]);
        $fileName = 'qr-cardapio-'.($restaurant->slug ?? 'restaurante').'.png';

        $png = QrCode::format('png')
            ->size(512)
            ->margin(2)
            ->generate($url);

        return response()->streamDownload(function () use ($png): void {
            echo $png;
        }, $fileName, ['Content-Type' => 'image/png']);
    }
}
