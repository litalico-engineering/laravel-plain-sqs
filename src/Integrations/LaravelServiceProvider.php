<?php

declare(strict_types=1);

namespace Dusterio\PlainSqs\Integrations;

use Dusterio\PlainSqs\Sqs\Connector;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

/**
 * Class CustomQueueServiceProvider
 */
class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/sqs-plain.php' => config_path('sqs-plain.php'),
        ]);

        Queue::after(static function (JobProcessed $event): void {
            $event->job->delete();
        });
    }

    public function register(): void
    {
        $this->app->booted(function (): void {
            $queue = $this->app->get('queue');
            if ($queue instanceof QueueManager) {
                $queue->extend('sqs-plain', static fn (): Connector => new Connector());
            }
        });
    }
}
