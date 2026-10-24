<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPreviewTest extends TestCase
{
    public function test_public_preview_explains_its_scope_without_a_database(): void
    {
        config(['deployment.mode' => 'public-preview']);
        $this->get('/')->assertOk()->assertSee('Website preview')->assertDontSee('Open AI Logistics Copilot');
        $this->get('/login')->assertOk()->assertSee('Online demo setup is pending.')
            ->assertSee('superadmin@netpack.test')->assertSee('partner@netpack.test')
            ->assertSee('Sign-in awaiting database setup');
        $this->post('/login', ['email' => 'superadmin@netpack.test', 'password' => 'Netpack!Admin#2026'])->assertStatus(503);
        $this->postJson('/api/payments/create', [])->assertStatus(503);
    }
}
