<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Prometa\Sleek\Facades\Sleek;
use Tests\TestCase;
use Workbench\App\Models\User;

class LayoutComponentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config()->set('app.name', 'Sleek Test App');
        Route::get('/login', fn () => '')->name('login');
        Route::get('/logout', fn () => '')->name('logout');
    }

    public function test_document_uses_the_app_name_by_default(): void
    {
        $this->blade('<x-sleek::document>Content</x-sleek::document>')
            ->assertSee('<title>Sleek Test App</title>', false);
    }

    public function test_view_adds_a_page_title_before_the_app_name(): void
    {
        $this->blade('<x-sleek::view title="Orders">Content</x-sleek::view>')
            ->assertSee('<title>Orders &#8211; Sleek Test App</title>', false);
    }

    public function test_navbar_uses_the_configured_authentication_guard(): void
    {
        config()->set('auth.guards.admin', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        Sleek::authentication()->guard('admin')->build();
        $this->actingAs($this->user(), 'admin');

        $this->blade('<x-sleek::navbar />')
            ->assertSee('Logout')
            ->assertDontSee('Login');
    }

    public function test_navbar_defaults_to_laravels_default_guard(): void
    {
        config()->set('auth.defaults.guard', 'admin');
        config()->set('auth.guards.admin', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        Sleek::authentication([]);
        $this->actingAs($this->user(), 'admin');

        $this->blade('<x-sleek::navbar />')
            ->assertSee('Logout')
            ->assertDontSee('Login');
    }

    public function test_view_forwards_nav_guard_to_the_navbar(): void
    {
        config()->set('auth.guards.admin', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        Sleek::authentication([]);
        $this->actingAs($this->user(), 'admin');

        $this->blade('<x-sleek::view nav:guard="admin">Content</x-sleek::view>')
            ->assertSee('Logout')
            ->assertDontSee('Login');
    }

    public function test_page_and_navbar_use_the_responsive_sidebar_classes(): void
    {
        $this->blade('<x-sleek::page>Content</x-sleek::page>')
            ->assertSee('class="layout layout-side"', false)
            ->assertSee('navbar-side', false);
    }

    private function user(): User
    {
        $user = new User();
        $user->id = 1;

        return $user;
    }
}
