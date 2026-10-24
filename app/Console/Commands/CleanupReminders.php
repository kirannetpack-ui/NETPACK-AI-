<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeliveryReminder;
use App\Models\ReminderLog;
use Carbon\Carbon;

class CleanupReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old sent reminders and logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Cleaning up old reminders...');
        
        // Delete sent reminders older than 30 days
        $deletedReminders = DeliveryReminder::where('is_sent', true)
            ->where('sent_at', '<', Carbon::now()->subDays(30))
            ->delete();
        
        // Delete reminder logs older than 60 days
        $deletedLogs = ReminderLog::where('created_at', '<', Carbon::now()->subDays(60))
            ->delete();
        
        $this->info("✅ Deleted {$deletedReminders} sent reminders older than 30 days");
        $this->info("✅ Deleted {$deletedLogs} reminder logs older than 60 days");
        
        return 0;
    }
}