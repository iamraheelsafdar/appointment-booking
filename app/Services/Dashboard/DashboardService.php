<?php

namespace App\Services\Dashboard;

use App\Interfaces\Dashboard\DashboardInterface;
use App\Models\User;

class DashboardService implements DashboardInterface
{
    public static function dashboard(): \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $userCount = User::count();
        $total = [
            'user_count' => $userCount,
        ];
        return view('backend.dashboard.dashboard', ['total' => $total]);
    }
}
