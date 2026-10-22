<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_can_open_the_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertOk()->assertSee('Selamat datang');
    }
}
