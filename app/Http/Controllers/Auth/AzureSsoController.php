<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AzureSsoController extends Controller
{
    /**
     * Send the user to Microsoft Entra ID to authenticate.
     */
    public function redirect()
    {
        abort_unless($this->enabled(), 404);

        return Socialite::driver('microsoft')->redirect();
    }

    /**
     * Handle the callback from Entra: match or provision the user, then log in.
     *
     * Provisioning happens once, on first SSO login. Subsequent logins never
     * re-sync roles or org — authorization is managed in-app from there on.
     */
    public function callback()
    {
        abort_unless($this->enabled(), 404);

        try {
            $azureUser = Socialite::driver('microsoft')->user();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('login')->withErrors([
                'email' => 'Single sign-on failed. Please try again or sign in with a password.',
            ]);
        }

        $email = $azureUser->getEmail();
        if (! $email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your Microsoft account did not return an email address.',
            ]);
        }

        // Match by Entra object id first, then by email (links existing accounts).
        $user = User::where('azure_oid', $azureUser->getId())->first()
            ?? User::where('email', $email)->first();

        if (! $user) {
            $user = $this->provision($azureUser->getName() ?: $email, $email);
            if (! $user) {
                return redirect()->route('login')->withErrors([
                    'email' => 'No organization is configured for new single sign-on users. Contact your administrator.',
                ]);
            }
        }

        if (! $user->is_active) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is inactive. Contact your administrator.',
            ]);
        }

        // Link the Entra identity on first SSO sign-in (provision-once).
        if (! $user->azure_oid) {
            $user->azure_oid = $azureUser->getId();
            $user->auth_provider = 'azure';
        }
        $user->last_login_at = now();
        $user->save();

        Auth::login($user, true);
        $request = request();
        $request->session()->regenerate();

        return redirect()->intended(
            $user->isMspStaff() ? route('staff.dashboard') : route('portal.dashboard')
        );
    }

    /**
     * Create a new end-user account in the default organization with the
     * baseline customer role, so they can immediately file and track tickets.
     */
    private function provision(string $name, string $email): ?User
    {
        $orgId = config('services.microsoft.default_org_id')
            ?: Organization::where('is_active', true)->where('is_msp', false)->orderBy('id')->value('id');

        if (! $orgId) {
            return null;
        }

        $user = User::create([
            'organization_id' => $orgId,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(40)), // unusable; SSO is the login path
            'is_active' => true,
            'auth_provider' => 'azure',
        ]);

        $user->assignRole('customer_user');

        return $user;
    }

    /**
     * SSO is enabled only when the Entra credentials are configured.
     */
    private function enabled(): bool
    {
        return filled(config('services.microsoft.client_id'))
            && filled(config('services.microsoft.client_secret'));
    }
}
