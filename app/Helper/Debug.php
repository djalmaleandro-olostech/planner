<?php

namespace App\Helper;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Context\ApplicationContext;

class Debug
{
    private static ?StdoutLoggerInterface $logger = null;

    private static array $init = [];

    private static function getLogger()
    {
        if (self::$logger === null) {
            self::$logger = ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
        }
    }

    public static function startMeasures(string $context): void
    {
        return; // Desativando no momento
        self::getLogger();
        $ctx = md5($context);
        self::$init[$ctx] = microtime(true);
        self::$logger->debug("[MEASURE] $context started");
    }


    public static function endMeasures(string $context): void
    {
        return; // Desativando no momento
        self::getLogger();
        $ctx = md5($context);
        $time = round(microtime(true) - self::$init[$ctx], 3) * 1000;
        self::$logger->debug("[MEASURE] $context - $time ms");
        self::$init[$ctx] = null;
    }

    public static function log($message, array $context = []): void
    {
        return; // Desativando no momento
        self::getLogger();

        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        self::$logger->debug($message, $context);
    }


}