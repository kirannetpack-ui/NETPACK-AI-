<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReminderService;

class ScheduleAllReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:schedule-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule reminders for all pending pickups';

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
        $this->info('🔄 Scheduling reminders for all pending pickups...');
        
        try {
            $count = $this->reminderService->scheduleAllReminders();
            $this->info("✅ Scheduled reminders for {$count} pickups successfully!");
        } catch (\Exception $e) {
            $this->error('❌ Error scheduling reminders: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}