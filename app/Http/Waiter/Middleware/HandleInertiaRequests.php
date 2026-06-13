<?php

namespace App\Http\Waiter\Middleware;

use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'waiter';
}
