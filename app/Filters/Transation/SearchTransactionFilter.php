<?php

namespace App\Filters\Transation;

use Closure;

class SearchTransactionFilter
{
    public function handle($query, Closure $next)
    {
        if (request()->filled('search')) {
            $searchTerm = urldecode(request()->input('search'));
            $query->where('session_id', 'like', '%' . $searchTerm . '%');
        }
        return $next($query);
    }
}
