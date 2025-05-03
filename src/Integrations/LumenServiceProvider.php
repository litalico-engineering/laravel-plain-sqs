<?php

declare(strict_types=1);

namespace Dusterio\PlainSqs\Integrations;

use Dusterio\PlainSqs\Sqs\Connector;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

/**
 * Class CustomQueueServiceProvider
 */
class LumenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     */
    public function boot(): void
    {
        Queue::after(static function (JobProcessed $event): void {
            $event->job->delete();
        });
    }

    public function register(): void
    {
        $this->app['queue']->addConnector('sqs-plain', static fn (): Connector => new Connector());
    }
}
