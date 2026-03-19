<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEventReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Event $event
    ) {}

    public function handle(): void
    {
        if ($this->event->reminder_sent) {
            return;
        }

        Mail::send('emails.event-reminder', [
            'event' => $this->event,
            'user' => $this->event->user
        ], function ($message) {
            $message->to($this->event->user->email)
                    ->subject('Rappel: ' . $this->event->title);
        });

        $this->event->update(['reminder_sent' => true]);
    }
}