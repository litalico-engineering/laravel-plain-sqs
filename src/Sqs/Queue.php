<?php

declare(strict_types=1);

namespace Dusterio\PlainSqs\Sqs;

use Dusterio\PlainSqs\ConfigHelper;
use Dusterio\PlainSqs\Jobs\DispatcherJob;
use Illuminate\Queue\InvalidPayloadException;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Queue\SqsQueue;
use JsonException;

use function is_array;
use function is_string;
use function PHPStan\dumpType;

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
        return match(true) {
            $job instanceof DispatcherJob && $job->isPlain() => json_encode($job->getPayload(), JSON_THROW_ON_ERROR),
            $job instanceof DispatcherJob => json_encode(['job' => "{$this->getClass($queue)}@handle", 'data' => $job->getPayload()], JSON_THROW_ON_ERROR),
            default => parent::createPayload($job, $queue, $data, $delay)
        };
    }

    private function getClass(string|null $queue = null): string
    {
        return match(true) {
            $queue === null => ConfigHelper::defaultHandler(),
            default => (static function (string $queue): string {
                $array = explode('/', $queue);

                return ConfigHelper::handlerByKey(end($array));
            })($queue),
        };
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

        if (!(isset($response['Messages']) && is_array($response['Messages']) && $response['Messages'] !== [])) {
            return null;
        }

        $queueIds = explode('/', $queue);
        $queueId = array_pop($queueIds);

        $class = ConfigHelper::handlerByKey($queueId);

        $payload = $response['Messages'][0] ?? [];
        dumpType($response['Messages']);

        $response = $this->modifyPayload($payload, $class);

        return new SqsJob($this->container, $this->sqs, $response, $this->connectionName, $queue);
    }

    /**
     * @throws JsonException
     */
    private function modifyPayload(string|array $payload, string $class): array
    {
        $body = match (true) {
            is_string($payload) => json_decode($payload, true, 512, JSON_THROW_ON_ERROR),
            isset($payload['Body']) && is_string($payload['Body']) => json_decode($payload['Body'], true, 512, JSON_THROW_ON_ERROR),
        };

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
