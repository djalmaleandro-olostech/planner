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
return [
    'default' => [
        'cloudfront_domain_signed' => Hyperf\Support\env('CLOUDFRONT_DOMAIN_SIGNED', "dev"),
        'cf_access_key_id' => Hyperf\Support\env('CF_ACCESS_KEY_ID', null),
        'cf_private_key' => Hyperf\Support\env('CF_PRIVATE_KEY', null),
        'expire' => Hyperf\Support\env('EXPIRE', time() + 86400),
        'cloudfront_domain' => Hyperf\Support\env('CLOUDFRONT_DOMAIN', "dev"),
        'cloudfront_domain_cover' => Hyperf\Support\env('CLOUDFRONT_DOMAIN_COVER', Hyperf\Support\env('CLOUDFRONT_DOMAIN', "dev")),
        'cloudfront_domain_hls' => Hyperf\Support\env('CLOUDFRONT_DOMAIN_HLS', Hyperf\Support\env('CLOUDFRONT_DOMAIN', "dev")),
        'cloudfront_domain_video' => Hyperf\Support\env('CLOUDFRONT_DOMAIN_VIDEO', Hyperf\Support\env('CLOUDFRONT_DOMAIN', "dev")),
    ],
];
