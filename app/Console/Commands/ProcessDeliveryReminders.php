<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReminderService;

class ProcessDeliveryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending delivery reminders and send notifications';

    /**
     * The reminder service instance.
     *
     * @var string
     */
    protected ReminderService $reminderService;

    /**
     * Create a new command instance.
     */
    public function __construct(ReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Processing delivery reminders...');
        
        try {
            $processed = $this->reminderService->processPendingReminders();
            $this->info("✅ Processed {$processed} reminders successfully!");
            
            // Also schedule reminders for new pickups
            $scheduled = $this->reminderService->scheduleAllReminders();
            $this->info("📋 Scheduled reminders for {$scheduled} pickups.");
            
        } catch (\Exception $e) {
            $this->error('❌ Error processing reminders: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
            return 1;
        }
        
        return 0;
    }
}