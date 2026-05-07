<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot(): void
    {
        Event::listen(MessageSending::class, function (MessageSending $event) {
            Log::info('mail.sending', [
                'to'      => array_keys($event->message->getTo()),
                'subject' => $event->message->getSubject(),
                'mailer'  => config('mail.default'),
                'host'    => config('mail.mailers.' . config('mail.default') . '.host'),
            ]);
        });

        Event::listen(MessageSent::class, function (MessageSent $event) {
            Log::info('mail.sent', [
                'to'      => array_keys($event->message->getTo()),
                'subject' => $event->message->getSubject(),
            ]);
        });

        Event::listen(JobFailed::class, function (JobFailed $event) {
            if (str_contains($event->job->resolveName(), 'Mail') || str_contains($event->job->resolveName(), 'Mailable')) {
                Log::error('mail.job_failed', [
                    'job'       => $event->job->resolveName(),
                    'exception' => $event->exception->getMessage(),
                    'mailer'    => config('mail.default'),
                    'host'      => config('mail.mailers.' . config('mail.default') . '.host'),
                ]);
            }
        });
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
