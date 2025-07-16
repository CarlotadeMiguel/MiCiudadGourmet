<?php
// tests/TestCase.php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Generar key para evitar MissingAppKeyException
        config(['app.key' => Str::random(32)]);
        $this->artisan('config:clear');
        $this->artisan('config:cache');

        // 2. Forzar la existencia de $errors en las vistas
        $errors = session('errors', new ViewErrorBag);
        view()->share('errors', $errors);
    }
}
