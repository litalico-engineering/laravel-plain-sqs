<?php

declare(strict_types=1);

use Illuminate\Queue\Jobs\Job;

/**
 * List of plain SQS queues and their corresponding handling classes
 */
return [
    'handlers' => [
        'base-integrations-updates' => Job::class,
    ],
    'default-handler' => Job::class,
];
