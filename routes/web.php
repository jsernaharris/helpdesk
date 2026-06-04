<?php

use App\Http\Controllers\Auth\AzureSsoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isMspStaff()) {
            return redirect()->route('staff.dashboard');
        }
        return redirect()->route('portal.dashboard');
    }
    return redirect()->route('login');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function () {
        $credentials = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, request()->boolean('remember'))) {
            request()->session()->regenerate();
            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            if ($user->isMspStaff()) {
                return redirect()->intended(route('staff.dashboard'));
            }
            return redirect()->intended(route('portal.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    });

    // Microsoft Entra ID single sign-on (routes 404 when SSO is not configured).
    Route::get('/auth/azure/redirect', [AzureSsoController::class, 'redirect'])->name('auth.azure.redirect');
    Route::get('/auth/azure/callback', [AzureSsoController::class, 'callback'])->name('auth.azure.callback');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');
