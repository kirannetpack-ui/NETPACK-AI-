<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InitialAdminRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_initial_super_administrator_can_be_created_from_temporary_environment_values(): void
    {
        $password = 'TestOnly!'.bin2hex(random_bytes(8));

        putenv('INITIAL_ADMIN_NAME=Initial Administrator');
        putenv('INITIAL_ADMIN_EMAIL=initial-admin@example.test');
        putenv("INITIAL_ADMIN_PASSWORD={$password}");

        try {
            $this->artisan('app:create-admin --from-env')
                ->expectsOutput('The initial super administrator has been created.')
                ->assertSuccessful();

            $administrator = User::query()->where('email', 'initial-admin@example.test')->firstOrFail();

            $this->assertSame(User::TYPE_SUPER_ADMIN, $administrator->user_type);
            $this->assertSame('approved', $administrator->verification_status);
            $this->assertTrue($administrator->registration_completed);
            $this->assertFalse($administrator->password_changed);
            $this->assertTrue(Hash::check($password, $administrator->password));

            $this->post(route('login.submit'), [
                'email' => $administrator->email,
                'password' => $password,
            ])->assertRedirect(route('password.change'));

            $this->artisan('app:create-admin --from-env')
                ->expectsOutput('The super administrator already exists. No changes were made.')
                ->assertSuccessful();

            $this->assertSame(1, User::query()->where('email', $administrator->email)->count());
        } finally {
            putenv('INITIAL_ADMIN_NAME');
            putenv('INITIAL_ADMIN_EMAIL');
            putenv('INITIAL_ADMIN_PASSWORD');
        }
    }
}
