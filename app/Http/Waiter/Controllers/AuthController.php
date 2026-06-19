<?php

namespace App\Http\Waiter\Controllers;

use App\Http\Waiter\Requests\LoginRequest;
use App\Models\Restaurant;
use App\Models\Waiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        if (! $waiter || ! Hash::check($request->input('password'), $waiter->password)) {
            return redirect()
                ->route('waiter.login', ['restaurant' => $restaurant->slug])
                ->withErrors(['username' => 'Usuário ou senha inválidos.']);
        }

        Auth::guard('waiter')->login($waiter);

        return redirect()->route('waiter.tables.index', ['restaurant' => $restaurant->slug]);
    }

    public function logout(Restaurant $restaurant): RedirectResponse
    {
        Auth::guard('waiter')->logout();

        return redirect()->route('waiter.login', ['restaurant' => $restaurant->slug]);
    }
}
