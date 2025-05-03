<?php

declare(strict_types=1);

namespace Dusterio\PlainSqs\Sqs;

use Dusterio\PlainSqs\ConfigHelper;
use Dusterio\PlainSqs\Jobs\DispatcherJob;
use Illuminate\Queue\InvalidPayloadException;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Queue\SqsQueue;
use Illuminate\Support\Facades\Config;
use JsonException;

use function array_key_exists;
use function count;
use function is_array;

/**
 * Class CustomSqsQueue
 */
class Queue extends SqsQueue
{
    /**
     * @inheritDoc
     * @throws InvalidPayloadException|JsonException
     */
    protected function createPayload($job, $queue, $data = '', $delay = null)
    {
        if (!$job instanceof DispatcherJob) {
            return parent::createPayload($job, $queue, $data, $delay);
        }

        $handlerJob = $this->getClass($queue) . '@handle';

        if ($job->isPlain()) {
            return json_encode($job->getPayload(), JSON_THROW_ON_ERROR);
        }

        return json_encode(['job' => $handlerJob, 'data' => $job->getPayload()], JSON_THROW_ON_ERROR);
    }

    private function getClass(string|null $queue = null): string
    {
        if (in_array($queue, ['0', '', null], true)) {
            return ConfigHelper::defaultHandler();
        }

        $array = explode('/', $queue);
        $queueKey = end($array);

        if (array_key_exists($queueKey, Config::get('sqs-plain.handlers'))) {
            return Config::get('sqs-plain.handlers')[$queueKey];
        }

        return ConfigHelper::defaultHandler();
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queue,
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        if (isset($response['Messages']) && count($response['Messages']) > 0) {
            $queueId = explode('/', $queue);
            $queueId = array_pop($queueId);

            $class = (array_key_exists($queueId, $this->container['config']->get('sqs-plain.handlers')))
                ? $this->container['config']->get('sqs-plain.handlers')[$queueId]
                : $this->container['config']->get('sqs-plain.default-handler');

            $response = $this->modifyPayload($response['Messages'][0], $class);

            return new SqsJob($this->container, $this->sqs, $response, $this->connectionName, $queue);
        }

        return null;
    }

    /**
     * @throws JsonException
     */
    private function modifyPayload(string|array $payload, string $class): array
    {
        if (! is_array($payload)) {
            $payload = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        }

        $body = json_decode($payload['Body'], true, 512, JSON_THROW_ON_ERROR);

        $body = [
            'job' => $class . '@handle',
            'data' => $body['data'] ?? $body,
            'uuid' => $payload['MessageId'],
        ];

        $payload['Body'] = json_encode($body, JSON_THROW_ON_ERROR);

        return $payload;
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $payload = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if (isset($payload['data'], $payload['job'])) {
            $payload = $payload['data'];
        }

        return parent::pushRaw(json_encode($payload, JSON_THROW_ON_ERROR), $queue, $options);
    }
}
