<?php

namespace App\Providers;

use App\Services\TenantContext;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        // Register the Microsoft Entra ID driver for Socialite.
        Event::listen(SocialiteWasCalled::class, [
            \SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class,
            'handle',
        ]);
    }
}
