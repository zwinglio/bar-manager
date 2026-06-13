<?php

use App\Http\Waiter\Controllers\AuthController;
use App\Http\Waiter\Controllers\TableController;
use App\Http\Waiter\Controllers\TableProductController;
use App\Http\Waiter\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;

Route::prefix('waiter/{restaurant:slug}')
    ->name('waiter.')
    ->middleware(HandleInertiaRequests::class)
    ->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.attempt');

        Route::middleware('waiter.auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');

            Route::get('tables', [TableController::class, 'index'])->name('tables.index');
            Route::get('tables/open', [TableController::class, 'create'])->name('tables.create');
            Route::post('tables', [TableController::class, 'store'])->name('tables.store');
            Route::get('tables/{table}', [TableController::class, 'show'])
                ->scopeBindings()->name('tables.show');
            Route::post('tables/{table}/close', [TableController::class, 'close'])
                ->scopeBindings()->name('tables.close');

            Route::post('tables/{table}/products', [TableProductController::class, 'store'])
                ->name('tables.products.store');
            Route::patch('tables/{table}/products/{product}', [TableProductController::class, 'update'])
                ->name('tables.products.update');
            Route::delete('tables/{table}/products/{product}', [TableProductController::class, 'destroy'])
                ->name('tables.products.destroy');
        });
    });
