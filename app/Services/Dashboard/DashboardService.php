<?php

namespace App\Services\Dashboard;

use App\Interfaces\Dashboard\DashboardInterface;
use App\Models\Appointment;
use App\Models\User;

class DashboardService implements DashboardInterface
{
    public static function dashboard(): \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $userCount = User::where('user_type', '!=', 'Admin')->count();
        $appointmentCount = Appointment::count();
        $total = [
            'user_count' => $userCount,
            'appointment_count' => $appointmentCount,
        ];
        return view('backend.dashboard.dashboard', ['total' => $total]);
    }
}
