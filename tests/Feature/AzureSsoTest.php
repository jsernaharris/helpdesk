<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class AzureSsoTest extends TestCase
{
    use RefreshDatabase;

    private Organization $customerOrg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->customerOrg = Organization::create(['name' => 'Acme', 'slug' => 'acme', 'is_msp' => false, 'is_active' => true]);
    }

    private function enableSso(): void
    {
        config([
            'services.microsoft.client_id' => 'test-client',
            'services.microsoft.client_secret' => 'test-secret',
            'services.microsoft.redirect' => 'http://localhost/auth/azure/callback',
        ]);
    }

    private function fakeAzureUser(string $id, string $email, ?string $name = 'SSO User'): void
    {
        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getId')->andReturn($id);
        $azureUser->shouldReceive('getEmail')->andReturn($email);
        $azureUser->shouldReceive('getName')->andReturn($name);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($azureUser);

        Socialite::shouldReceive('driver')->with('microsoft')->andReturn($provider);
    }

    public function test_sso_routes_404_when_not_configured(): void
    {
        $this->get(route('auth.azure.redirect'))->assertNotFound();
        $this->get(route('auth.azure.callback'))->assertNotFound();
    }

    public function test_login_page_shows_microsoft_button_only_when_configured(): void
    {
        $this->get(route('login'))->assertDontSee('Sign in with Microsoft');

        $this->enableSso();
        $this->get(route('login'))->assertSee('Sign in with Microsoft');
    }

    public function test_new_sso_user_is_provisioned_as_customer_in_default_org(): void
    {
        $this->enableSso();
        config(['services.microsoft.default_org_id' => $this->customerOrg->id]);
        $this->fakeAzureUser('oid-new', 'newperson@acme.com', 'New Person');

        $this->get(route('auth.azure.callback'))
            ->assertRedirect(route('portal.dashboard'));

        $user = User::where('email', 'newperson@acme.com')->first();
        $this->assertNotNull($user);
        $this->assertSame($this->customerOrg->id, $user->organization_id);
        $this->assertSame('oid-new', $user->azure_oid);
        $this->assertSame('azure', $user->auth_provider);
        $this->assertTrue($user->hasRole('customer_user'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_user_is_matched_by_email_and_keeps_roles(): void
    {
        $this->enableSso();
        $existing = User::create([
            'organization_id' => $this->customerOrg->id,
            'name' => 'Existing', 'email' => 'existing@acme.com',
            'password' => bcrypt('secret'), 'is_active' => true,
        ]);
        $existing->assignRole('customer_admin');

        $this->fakeAzureUser('oid-existing', 'existing@acme.com');

        $this->get(route('auth.azure.callback'))->assertRedirect(route('portal.dashboard'));

        $existing->refresh();
        $this->assertSame('oid-existing', $existing->azure_oid);
        $this->assertTrue($existing->hasRole('customer_admin'));
        $this->assertFalse($existing->hasRole('customer_user'));
        $this->assertAuthenticatedAs($existing);
    }

    public function test_inactive_user_cannot_sign_in(): void
    {
        $this->enableSso();
        $user = User::create([
            'organization_id' => $this->customerOrg->id,
            'name' => 'Disabled', 'email' => 'disabled@acme.com',
            'password' => bcrypt('secret'), 'is_active' => false,
        ]);
        $user->assignRole('customer_user');

        $this->fakeAzureUser('oid-disabled', 'disabled@acme.com');

        $this->get(route('auth.azure.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
