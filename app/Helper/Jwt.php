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
namespace App\Helper;

use Carbon\Carbon;
use Firebase\JWT\Key;
use Firebase\JWT\JWT as FirebaseJWT;
use Hyperf\Config\ConfigFactory;
use Hyperf\Context\ApplicationContext;

class Jwt
{
    /**
     * @const ALG
     */
    public const ALG = 'HS256';

    private string $jwtSecret;

    public function __construct()
    {
        $config = new ConfigFactory();
        $config = $config(ApplicationContext::getContainer());
        $this->jwtSecret = $config->get('jwt.default.secret');
    }

    public function decode(string $encoded): object
    {
        return FirebaseJWT::decode(
            $encoded,
            new Key(
                $this->jwtSecret,
                self::ALG
            )
        );
    }

    public function encode(array $data): string
    {  
        $data['validade'] = Carbon::now()->addMinutes(15)->toDateTimeString();
        return FirebaseJWT::encode(
            $data,
            $this->jwtSecret,
            self::ALG
        );
    }
}