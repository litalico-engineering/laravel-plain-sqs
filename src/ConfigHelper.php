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
        if (
            is_string($value) &&
            class_exists($value) &&
            is_a($value, Job::class, true)
        ) {
            return $value;
        }

        throw new RuntimeException('sqs-plain.default-handler should be a class string');
    }

    /**
     * @return array<string, class-string<Job>>
     */
    public static function handlers(): array
    {
        $handlers = Config::get('sqs-plain.handlers');
        if (!is_array($handlers)) {
            throw new RuntimeException('sqs-plain.default-handler should be an array');
        }

        $response = [];
        foreach ($handlers as $key => $handler) {
            if (
                !(is_string($key) && is_string($handler) && class_exists($handler) && is_a($handler, Job::class, true))
            ) {
                throw new RuntimeException('sqs-plain.handlers should be an array of class strings');
            }

            $response[$key] = $handler;
        }

        return $response;
    }

    /**
     * @return class-string<Job>
     */
    public static function handlerByKey(string|null $key): string
    {
        return match(true) {
            $key === null => self::defaultHandler(),
            default => self::handlers()[$key] ?? self::defaultHandler(),
        };
    }
}
