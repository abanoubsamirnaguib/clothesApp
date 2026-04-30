<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_admin_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('admin.login'));
        $this->assertTrue(Route::has('admin.dashboard'));
    }
}
