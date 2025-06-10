<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckDueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for tasks that are due soon and send notifications';

    public function __construct(private WebPushService $webPushService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Check if current time is between 19:00 - 23:59
        $eveningStart = $now->copy()->setTime(19, 0, 0);
        $eveningEnd = $now->copy()->setTime(23, 59, 59);
        $isEveningTime = $now->between($eveningStart, $eveningEnd);

        $this->info("Current time: " . $now->format('H:i'));
        $this->info("Evening time range: 19:00 - 23:59");
        $this->info("Is evening time: " . ($isEveningTime ? 'Yes' : 'No'));

        // Only proceed if it's evening time
        if (!$isEveningTime) {
            $this->info("Not evening time - notifications will not be sent");
            return;
        }

        // Get tasks due today only
        $dueTasks = Task::with('user')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'expired')
            ->whereDate('due_date', $now->toDateString())
            ->get();

        $notificationsSent = 0;

        foreach ($dueTasks as $task) {
            $success = $this->webPushService->sendTaskDueReminder($task->user, $task);

            if ($success) {
                $notificationsSent++;
            }
        }

        $this->info("Checked {$dueTasks->count()} due tasks for today, sent {$notificationsSent} notifications");
    }
}
