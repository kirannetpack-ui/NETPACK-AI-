<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPreviewTest extends TestCase
{
    public function test_public_preview_explains_its_scope_without_a_database(): void
    {
        config(['deployment.mode' => 'public-preview']);
        $this->get('/')->assertOk()->assertSee('Website preview')->assertDontSee('Open AI Logistics Copilot');
        $this->get('/login')->assertStatus(503)->assertSee('Our workspace is getting ready.');
        $this->postJson('/api/payments/create', [])->assertStatus(503);
    }
}
