<?php

declare(strict_types=1);

/**
 * List of plain SQS queues and their corresponding handling classes
 */
return [
    'handlers' => [
        'base-integrations-updates' => \Illuminate\Queue\Jobs\Job::class,
    ],
    'default-handler' => \Illuminate\Queue\Jobs\Job::class
];
