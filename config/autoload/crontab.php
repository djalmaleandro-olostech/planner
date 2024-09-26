<?php

use Hyperf\Crontab\Crontab;
use App\Task\Warmup;

return [
    'enable' => Hyperf\Support\env("ACTIVE_CRON", false),
    // Timed tasks defined by configuration
    'crontab' => [
        // (new Crontab())
        //     ->setName('Cron Name')
        //     ->setRule('30 3 * * *')
        //     ->setCallback([Warmup::class, 'class'])
        //     ->setMemo('A memo'),
    ],
];