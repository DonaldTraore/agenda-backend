<?php

namespace App\Console\Commands;

use App\Jobs\SendEventReminderJob;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';
    protected $description = 'Envoie les rappels d\'événements par email';

    public function handle()
    {
        $now = Carbon::now();
        
        $events = Event::where('has_reminder', true)
            ->where('reminder_sent', false)
            ->whereRaw('TIMESTAMPDIFF(MINUTE, NOW(), start_date) <= reminder_minutes')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, NOW(), start_date) > 0')
            ->with('user')
            ->get();

        foreach ($events as $event) {
            SendEventReminderJob::dispatch($event);
        }

        $this->info("Traitement de {$events->count()} rappels d'événements");
    }
}