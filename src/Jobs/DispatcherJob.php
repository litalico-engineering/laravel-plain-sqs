<?php

declare(strict_types=1);

namespace Dusterio\PlainSqs\Jobs;

use Dusterio\PlainSqs\ConfigHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatcherJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected bool $plain = false;

    /**
     * @param array<array-key, string> $data
     */
    public function __construct(
        protected readonly array $data
    ) {
    }

    /**
     * @return array<array-key, string>|array{job: string, data: array<array-key, string>}
     */
    public function getPayload(): array
    {
        if ($this->isPlain()) {
            return $this->data;
        }

        return [
            'job' => ConfigHelper::defaultHandler(),
            'data' => $this->data,
        ];
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
