<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Service;

use Hyperf\Contract\StdoutLoggerInterface;

class WarmupService
{
    private StdoutLoggerInterface $logger;
    public function __construct(
        StdoutLoggerInterface $logger
    ) {
        $this->logger = $logger;
    }
}
