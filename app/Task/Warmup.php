<?php

declare(strict_types=1);

namespace App\Task;

use App\Service\WarmupService;
use Hyperf\Contract\StdoutLoggerInterface;

class Warmup
{
    private WarmupService $warmupService;
    private StdoutLoggerInterface $logger;
    private $startTime;

    public function __construct(
        WarmupService $warmupService,
        StdoutLoggerInterface $logger
    )
    {
        $this->warmupService = $warmupService;
        $this->logger = $logger;
        $this->startTime = microtime(true);
    }
}