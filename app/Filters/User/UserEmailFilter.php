<?php

namespace App\Filters\User;

use Closure;

class UserEmailFilter
{
    public function handle($query, Closure $next)
    {
        if (request()->filled('search')) {
            $searchTerm = urldecode(request()->input('search'));
            $query->orWhere(function ($q) use ($searchTerm) {
                $q->Where('email', 'like', '%' . $searchTerm . '%');
            });
        }
        return $next($query);
    }
}
