<?php

declare(strict_types=1);

namespace Dusterio\PlainSqs\Sqs;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Support\Arr;

class Connector extends SqsConnector
{
    /**
     * @inheritDoc
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if (isset($config['key'], $config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        return new Queue(
            new SqsClient($config),
            $config['queue'],
            Arr::get($config, 'prefix', '')
        );
    }
}
