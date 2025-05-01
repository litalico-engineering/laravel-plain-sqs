<?php
declare(strict_types=1);

namespace Dusterio\PlainSqs\Jobs;

use Illuminate\Queue\Jobs\SqsJob;

class CustomPlainSqsJob extends SqsJob
{
    /**
     * @inheritDoc
     */
    public function resolveQueuedJobClass()
    {
        return parent::resolveQueuedJobClass();
    }
}