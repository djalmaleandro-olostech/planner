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

class StringHelper
{
    public static function getJson(?string $str): ?string
    {
        $pattern = '/\{.*?}/s';

        if (preg_match($pattern, $str, $matches)) {
            return $matches[0];
        }

        return null;
    }

    public static function isJson(?string $str): bool
    {
        json_decode($str);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
