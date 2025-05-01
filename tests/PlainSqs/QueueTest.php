<?php

namespace Tests\PlainSqs;

use Aws\Sqs\SqsClient;
use Dusterio\PlainSqs\Jobs\DispatcherJob;
use Dusterio\PlainSqs\Sqs\Queue;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\FullAccessWrapper;

/**
 * Class QueueTest
 * @package Dusterio\PlainSqs\Tests
 */
class QueueTest extends TestCase
{
    /**
     * @throws
     */
    #[Test]
    public function class_named_is_derived_from_queue_name(): void
    {
        setup:
        $sqsMock = $this->createMock(SqsClient::class);
        $content = [
            'test' => 'test'
        ];

        Config::shouldReceive('get')
            ->once()
            ->with('sqs-plain.handlers')
            ->andReturn([]);

        Config::shouldReceive('get')
            ->twice()
            ->with('sqs-plain.default-handler')
            ->andReturn('duummy');

        $job = new DispatcherJob($content);

        $queue = new Queue($sqsMock,'test-queue');

        /** @var Queue&FullAccessWrapper $instance */
        $instance = new FullAccessWrapper($queue);

        when:
        $actual = $instance->createPayload($job, 'test-queue', json_encode($content, JSON_THROW_ON_ERROR), 0);

        then:
        self::assertSame(
            '{"job":"duummy@handle","data":{"job":"duummy","data":{"test":"test"}}}',
            $actual,
        );
    }
}