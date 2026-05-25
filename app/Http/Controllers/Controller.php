<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function formatPagination($paginator)
    {
        return [
            'total' => $paginator->total(),
            'count_items' => $paginator->count(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'data' => $paginator->items(),
        ];
    }
}
