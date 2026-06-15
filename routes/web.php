<?php

use App\Http\Controllers\PublicMenuController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('landing');

Route::get('menu/{restaurant:slug}', [PublicMenuController::class, 'show'])
    ->name('menu.public');

Route::get('restaurant/menu/{restaurant}/qr-download', [PublicMenuController::class, 'downloadQr'])
    ->middleware('auth')
    ->name('restaurant.menu.qr-download');
