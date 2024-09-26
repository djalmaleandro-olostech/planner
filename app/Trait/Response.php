<?php

namespace App\Trait;

trait Response
{
    private function responsePosts(array $items, array $pagination): array
    {
        return [
            '_pagination' => $pagination,
            'items' => $items
        ];
    }
}
