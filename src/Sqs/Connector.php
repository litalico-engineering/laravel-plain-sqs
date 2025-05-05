<?php

declare(strict_types=1);

namespace Dusterio\PlainSqs\Sqs;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Support\Arr;

use function is_string;

class Connector extends SqsConnector
{
    /**
     * @inheritDoc
     * @param array<string, mixed> $config
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if (isset($config['key'], $config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        // Ensure queue is a string
        $queueValue = $config['queue'] ?? null;
        $queue = match(true) {
            is_string($queueValue) => $queueValue,
            default => '',
        };

        // Ensure prefix is a string
        $prefixValue = $config['prefix'] ?? null;
        $prefix = match(true) {
            is_string($prefixValue) => $prefixValue,
            default => '',
        };

        return new Queue(
            new SqsClient($config),
            $queue,
            $prefix
        );
    }
}
