<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Generar APP_KEY para evitar MissingAppKeyException
        config(['app.key' => Str::random(32)]);
        $this->artisan('config:clear');
        $this->artisan('config:cache');
    }
}
