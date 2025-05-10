<?php

declare(strict_types=1);

namespace Dusterio\PlainSqs;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Config;
use RuntimeException;

use function is_array;
use function is_string;

class ConfigHelper
{
    /**
     * @return class-string<Job>
     */
    public static function defaultHandler(): string
    {
        $value = Config::get('sqs-plain.default-handler');

        return match (true) {
            is_string($value) &&
            class_exists($value) &&
            is_a($value, Job::class, true) => $value,
            default => throw new RuntimeException('sqs-plain.default-handler should be a class string'),
        };
    }

    /**
     * @return array<string, class-string<Job>>
     */
    public static function handlers(): array
    {
        $handlers = Config::get('sqs-plain.handlers');
        $arrayHandlers = match(true) {
            is_array($handlers) => $handlers,
            default => throw new RuntimeException('sqs-plain.handlers should be an array of class strings'),
        };

        $response = [];
        foreach ($arrayHandlers as $key => $handler) {
            match(true) {
                is_string($key) &&
                is_string($handler) &&
                is_a($handler, Job::class, true) => $response[$key] = $handler,
                default => throw new RuntimeException('sqs-plain.handlers should be an array of class strings'),
            };
        }

        return $response;
    }

    /**
     * @return class-string<Job>
     */
    public static function handlerByKey(string|null $key): string
    {
        return match (true) {
            isset(self::handlers()[$key]) => self::handlers()[$key],
            default => self::defaultHandler(),
        };
    }
}
