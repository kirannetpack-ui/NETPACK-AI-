<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeployedAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_native_login_issues_a_usable_token_without_a_browser_session(): void
    {
        $user = User::factory()->create([
            'email' => 'native@example.test',
            'password' => bcrypt('Native!Test#2026'),
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email, 'password' => 'Native!Test#2026',
        ])->assertOk()->assertJson(['success' => true, 'token_type' => 'Bearer']);

        $token = $login->json('token');
        $this->assertNotEmpty($token);
        $this->assertGuest('web');
        $this->withToken($token)->getJson('/api/user')
            ->assertOk()->assertJsonPath('data.id', $user->id);
        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_native_login_rejects_wrong_password_and_unapproved_accounts(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Native!Test#2026'),
            'verification_status' => 'pending',
            'password_changed' => true,
        ]);
        $this->postJson('/api/auth/login', [
            'email' => $user->email, 'password' => 'wrong-password',
        ])->assertUnauthorized();
        $this->postJson('/api/auth/login', [
            'email' => $user->email, 'password' => 'Native!Test#2026',
        ])->assertForbidden();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_public_and_sign_in_pages_expose_pwa_installation_metadata(): void
    {
        foreach (['/', '/login'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee('rel="manifest"', false)
                ->assertSee('apple-mobile-web-app-capable', false);
        }
    }
}
