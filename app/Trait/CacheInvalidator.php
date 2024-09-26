<?php

namespace App\Trait;

trait CacheInvalidator
{
    public function invalidateCache(string $field, bool $cacheable = true, string $key = ''): void
    {   
        if($cacheable){
            $redisResult = $this->redis->keys("c:*:$field");
            foreach ($redisResult as $redisKey) {
                $this->redis->del($redisKey);
            }
        } else {
            $this->redis->hdel($key, $field);
        }
    }
}
