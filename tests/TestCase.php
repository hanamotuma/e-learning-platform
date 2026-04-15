<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @method \Illuminate\Testing\TestResponse get(string $uri, array $headers = [])
 * @method \Illuminate\Testing\TestResponse post(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse put(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse patch(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse delete(string $uri, array $data = [], array $headers = [])
 * @method void assertAuthenticated(string|null $guard = null)
 * @method void assertGuest(string|null $guard = null)
 * @method void assertDatabaseHas(string $table, array $data)
 * @method \Illuminate\Testing\TestResponse actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, string|null $guard = null)
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Perform any additional setup before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
}
