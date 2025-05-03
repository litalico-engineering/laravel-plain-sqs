<?php
declare(strict_types=1);

namespace Dusterio\PlainSqs;

use Illuminate\Support\Facades\Config;
use RuntimeException;

class ConfigHelper
{
    /**
     * @retrun class-string<\Illuminate\Queue\Jobs\Job>
     */
    public static function defaultHandler(): string
    {
        $value = Config::get('sqs-plain.default-handler');
        if (is_string($value)) {
            return $value;
        }

        throw new RuntimeException('sqs-plain.default-handler should be a class string');
    }

    public static function findByQueue(string $key): string
    {
        return Config::get('sqs-plain.handlers')[$key] ?? [];
    }
}
