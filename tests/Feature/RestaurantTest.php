<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Restaurant;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function home_returns_200(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function restaurants_index_returns_200(): void
    {
        // Crear datos de prueba
        Restaurant::factory()->count(2)->create();

        $response = $this->get('/restaurants');
        $response->assertStatus(200);
    }

    /** @test */
    public function restaurant_show_returns_404_for_nonexistent(): void
    {
        $response = $this->get('/restaurants/9999');
        $response->assertStatus(404);
    }
}
