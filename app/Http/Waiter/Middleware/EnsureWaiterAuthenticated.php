<?php

namespace App\Http\Waiter\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWaiterAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurant = $request->route('restaurant');

        if (! $restaurant instanceof Restaurant) {
            $restaurant = Restaurant::where('slug', $restaurant)->firstOrFail();
        }

        if (auth('waiter')->guest()) {
            return redirect()->route('waiter.login', ['restaurant' => $restaurant->slug]);
        }

        $waiter = auth('waiter')->user();

        if ($waiter->restaurant_id !== $restaurant->id) {
            auth('waiter')->logout();

            return redirect()->route('waiter.login', ['restaurant' => $restaurant->slug]);
        }

        return $next($request);
    }
}
