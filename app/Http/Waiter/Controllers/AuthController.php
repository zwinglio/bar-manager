<?php

namespace App\Http\Waiter\Controllers;

use App\Http\Waiter\Requests\LoginRequest;
use App\Models\Restaurant;
use App\Models\Waiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController
{
    public function showLogin(Restaurant $restaurant): Response
    {
        return Inertia::render('Login', [
            'restaurant' => $restaurant->only('name', 'slug'),
        ]);
    }

    public function login(LoginRequest $request, Restaurant $restaurant): RedirectResponse
    {
        /** @var Waiter|null $waiter */
        $waiter = Waiter::where('restaurant_id', $restaurant->id)
            ->where('username', $request->input('username'))
            ->where('is_active', true)
            ->first();

        if (! $waiter) {
            return redirect()
                ->route('waiter.login', ['restaurant' => $restaurant->slug])
                ->withErrors(['username' => 'Invalid username.']);
        }

        // TODO: add password check when passwords are introduced
        Auth::guard('waiter')->login($waiter);

        return redirect()->route('waiter.tables.index', ['restaurant' => $restaurant->slug]);
    }

    public function logout(Restaurant $restaurant): RedirectResponse
    {
        Auth::guard('waiter')->logout();

        return redirect()->route('waiter.login', ['restaurant' => $restaurant->slug]);
    }
}
