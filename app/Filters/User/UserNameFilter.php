<?php

namespace App\Filters\User;

use Closure;

class UserNameFilter
{
    public function handle($query, Closure $next)
    {
        if (request()->filled('search')) {
            $searchTerm = urldecode(request()->input('search'));
            $query->orWhere(function ($q) use ($searchTerm) {
                $q->Where('name', 'like', '%' . $searchTerm . '%');
            });
        }
        return $next($query);
    }
}
