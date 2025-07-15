<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Restaurant;

class RestaurantUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function restaurant_model_count_is_integer(): void
    {
        Restaurant::factory()->count(3)->create();
        $this->assertIsInt(Restaurant::count());
    }
}
