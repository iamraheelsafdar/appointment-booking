<?php

namespace App\Filters\Appointment;

use Closure;

class AppointmentDateFilter
{
    public function handle($query, Closure $next)
    {
        if (request()->filled('start_date') && request()->filled('end_date')) {
            $query->whereBetween('selected_date', [request()->input('start_date'), request()->input('end_date')]);
        }
        return $next($query);
    }
}
