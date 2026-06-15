<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\RestaurantPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    RestaurantPanelProvider::class,
];
