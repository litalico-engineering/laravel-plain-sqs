<?php

declare(strict_types=1);

namespace Dusterio\PlainSqs\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class DispatcherJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected mixed $data;

    protected bool $plain = false;

    /**
     * DispatchedJob constructor.
     */
    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    public function getPayload()
    {
        if (! $this->isPlain()) {
            return [
                'job' => Config::get('sqs-plain.default-handler'),
                'data' => $this->data,
            ];
        }

        return $this->data;
    }

    /**
     * @return $this
     */
    public function setPlain(bool $plain = true): self
    {
        $this->plain = $plain;

        return $this;
    }

    public function isPlain(): bool
    {
        return $this->plain;
    }
}
