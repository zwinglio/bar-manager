<?php

use App\Http\Controllers\PublicMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('menu/{restaurant:slug}', [PublicMenuController::class, 'show'])
    ->name('menu.public');
