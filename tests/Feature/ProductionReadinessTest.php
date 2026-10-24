<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    public function test_health_and_readiness_endpoints_are_available(): void
    {
        $this->getJson(route('api.health'))
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);

        $this->getJson(route('api.readiness'))
            ->assertOk()
            ->assertJson([
                'status' => 'ready',
                'checks' => [
                    'database' => true,
                    'cache' => true,
                ],
            ]);
    }

    public function test_security_headers_are_added_to_responses(): void
    {
        $this->getJson(route('api.health'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(self), geolocation=(self)');
    }

    public function test_production_check_accepts_safe_critical_configuration(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config()->set([
            'app.debug' => false,
            'app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'app.url' => 'https://courier.example.com',
            'session.secure' => true,
            'session.driver' => 'redis',
            'database.default' => 'mysql',
            'queue.default' => 'redis',
            'cache.default' => 'redis',
            'mail.default' => 'postmark',
        ]);

        $this->artisan('app:production-check')
            ->expectsOutput('Critical deployment configuration checks passed.')
            ->assertSuccessful();
    }

    public function test_production_check_can_validate_a_hardened_staging_environment(): void
    {
        $this->app->detectEnvironment(fn (): string => 'staging');
        config()->set([
            'app.debug' => false,
            'app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'app.url' => 'https://staging.courier.example.com',
            'session.secure' => true,
            'session.driver' => 'redis',
            'database.default' => 'mysql',
            'queue.default' => 'redis',
            'cache.default' => 'redis',
            'mail.default' => 'postmark',
        ]);

        $this->artisan('app:production-check --allow-staging')
            ->expectsOutput('Critical deployment configuration checks passed.')
            ->assertSuccessful();
    }

    public function test_production_check_rejects_unsafe_configuration(): void
    {
        config()->set([
            'app.debug' => true,
            'app.key' => null,
            'app.url' => 'http://localhost',
            'session.secure' => false,
            'database.default' => 'sqlite',
            'queue.default' => 'sync',
            'mail.default' => 'log',
        ]);

        $this->artisan('app:production-check')->assertFailed();
    }
}
