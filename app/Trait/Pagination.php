<?php

namespace App\Trait;

trait Pagination
{
    private function getPagination(
        int $totalItems,
        int $page,
        int $limit
    ): array 
    {
        $totalPages = (int)round($totalItems / $limit);
        $pagination = [];
        $pagination['totalItems'] = $totalItems;
        $pagination['page'] = $page;
        $pagination['lastPage'] = $totalPages;
        $pagination['limit'] = $limit;
        $pagination['hasMoreItems'] = false;

        if ($page < $totalPages) {
            $pagination['nextPage'] = $page + 1;
            $pagination['hasMoreItems'] = true;
        }
        return $pagination;
    }
}
