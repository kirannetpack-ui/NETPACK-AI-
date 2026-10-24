<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrivatizeKycDocuments extends Command
{
    protected $signature = 'storage:privatize-kyc {--apply : Move verified copies out of public storage}';

    protected $description = 'Inventory or move legacy public KYC files to private storage without changing paths';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('private');
        $files = $public->allFiles('kyc');
        $this->info(count($files).' public KYC files found.');
        if (! $this->option('apply')) {
            $this->info('Preview only. Back up storage, then run with --apply during maintenance.');

            return self::SUCCESS;
        }
        foreach ($files as $path) {
            $contents = $public->get($path);
            if (! $private->exists($path)) {
                $private->put($path, $contents);
            }
            if (! hash_equals(hash('sha256', $contents), hash('sha256', $private->get($path)))) {
                $this->error('Private copy mismatch; public original retained. Resolve the conflict before retrying.');

                return self::FAILURE;
            }
            if (! $public->delete($path)) {
                $this->error('Public original could not be removed.');

                return self::FAILURE;
            }
        }
        $this->info('Verified KYC files moved to private storage.');

        return self::SUCCESS;
    }
}
