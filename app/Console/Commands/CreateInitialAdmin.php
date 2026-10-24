<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateInitialAdmin extends Command
{
    protected $signature = 'app:create-admin
                            {--from-env : Read the temporary INITIAL_ADMIN_* environment variables}
                            {--reset-password : Reset the password only when the matching super administrator already exists}';

    protected $description = 'Create the initial super administrator without recording credentials in source control or logs.';

    public function handle(): int
    {
        $fromEnvironment = (bool) $this->option('from-env');

        $name = $fromEnvironment ? getenv('INITIAL_ADMIN_NAME') : $this->ask('Administrator name');
        $email = $fromEnvironment ? getenv('INITIAL_ADMIN_EMAIL') : $this->ask('Administrator email');
        $password = $fromEnvironment ? getenv('INITIAL_ADMIN_PASSWORD') : $this->secret('Administrator password');

        if (! $fromEnvironment && $password !== $this->secret('Confirm administrator password')) {
            $this->error('The password confirmation does not match.');

            return self::FAILURE;
        }

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser !== null) {
            if ($existingUser->user_type !== User::TYPE_SUPER_ADMIN) {
                $this->error('An account with this email already exists and is not a super administrator. No changes were made.');

                return self::FAILURE;
            }

            if (! $this->option('reset-password')) {
                $this->info('The super administrator already exists. No changes were made.');

                return self::SUCCESS;
            }

            $existingUser->forceFill([
                'password' => Hash::make($password),
                'password_changed' => false,
                'verification_status' => 'approved',
                'registration_completed' => true,
            ])->save();

            $this->info('The super administrator password has been reset.');

            return self::SUCCESS;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'user_type' => User::TYPE_SUPER_ADMIN,
            'verification_status' => 'approved',
            'registration_completed' => true,
            'password_changed' => false,
        ]);

        $this->info('The initial super administrator has been created.');

        return self::SUCCESS;
    }
}
