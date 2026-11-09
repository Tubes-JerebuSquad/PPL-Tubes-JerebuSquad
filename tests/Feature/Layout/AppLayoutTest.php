<?php

namespace Tests\Feature\Layout;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AppLayoutTest extends TestCase
{
    public function test_layout_aplikasi_menampilkan_shell_dan_navigasi_mobile(): void
    {
        Route::middleware('web')->get('/__layout-app', fn () => view('layouts.app'));

        $this->get('/__layout-app')
            ->assertOk()
            ->assertSee('RimbaWatch')
            ->assertSee('Sistem demo')
            ->assertSee('Mode demo akademik')
            ->assertSee('data-buka-sidebar', false)
            ->assertSee('sidebar-mobile', false)
            ->assertSee('aria-disabled="true"', false);
    }

    public function test_layout_guest_menampilkan_brand_dan_slot_konten(): void
    {
        Route::middleware('web')->get('/__layout-guest', fn () => view('layouts.guest'));

        $this->get('/__layout-guest')
            ->assertOk()
            ->assertSee('RimbaWatch')
            ->assertSee('Pantau hutan')
            ->assertSee('Mode demo akademik');
    }
}
