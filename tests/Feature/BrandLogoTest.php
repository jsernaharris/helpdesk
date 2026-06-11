<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandLogoTest extends TestCase
{
    public function test_login_shows_app_name_text_when_no_logo_configured(): void
    {
        config(['app.name' => 'Acme Helpdesk', 'app.logo' => null]);

        $response = $this->get(route('login'));

        $response->assertSee('Acme Helpdesk');
        $response->assertDontSee('<img', false);
    }

    public function test_login_renders_logo_image_when_configured(): void
    {
        config(['app.name' => 'Acme Helpdesk', 'app.logo' => 'images/logo.svg']);

        $response = $this->get(route('login'));

        $response->assertSee(asset('images/logo.svg'), false);
        $response->assertSee('alt="Acme Helpdesk"', false);
    }

    public function test_full_url_logo_is_used_verbatim(): void
    {
        config(['app.logo' => 'https://cdn.example.com/logo.png']);

        $response = $this->get(route('login'));

        $response->assertSee('https://cdn.example.com/logo.png', false);
    }
}
