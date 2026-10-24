<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProductionReadinessCheck extends Command
{
    protected $signature = 'app:production-check {--allow-staging : Apply the same checks to a staging environment}';

    protected $description = 'Fail when critical production configuration is unsafe';

    public function handle(): int
    {
        $errors = [];
        $warnings = [];

        $isProduction = app()->environment('production');
        $isApprovedStaging = $this->option('allow-staging') && app()->environment('staging');

        $this->require(
            $errors,
            $isProduction || $isApprovedStaging,
            'APP_ENV must be production (or staging when --allow-staging is supplied).'
        );
        $this->require($errors, config('app.debug') === false, 'APP_DEBUG must be false.');
        $this->require($errors, filled(config('app.key')), 'APP_KEY must be generated.');
        $this->require($errors, config('app.key') !== 'base64:7genAr6vdKRc37ptrhKOuTO5+aDkJIL96kUUGqV11F8=', 'Replace the published demo APP_KEY with a unique deployment key.');
        $this->require($errors, str_starts_with((string) config('app.url'), 'https://'), 'APP_URL must use HTTPS.');
        $this->require($errors, config('session.secure') === true, 'SESSION_SECURE_COOKIE must be true.');
        $this->require($errors, config('database.default') !== 'sqlite', 'Production must not use SQLite.');
        $this->require($errors, config('queue.default') !== 'sync', 'Production queues must not use the sync driver.');
        $this->require($errors, ! in_array(config('mail.default'), ['array', 'log'], true), 'Configure a production mail transport.');

        if (config('cache.default') === 'file') {
            $warnings[] = 'File cache is acceptable only for a small single-server pilot; Redis is recommended.';
        }

        if (config('session.driver') === 'file') {
            $warnings[] = 'File sessions require sticky single-server deployment; Redis is recommended.';
        }

        foreach ($warnings as $warning) {
            $this->warn($warning);
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info('Critical deployment configuration checks passed.');

        return self::SUCCESS;
    }

    private function require(array &$errors, bool $condition, string $message): void
    {
        if (! $condition) {
            $errors[] = $message;
        }
    }
}
