<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RejectPendingBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reject-pending-booking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is use to reject all the pending appointment after 10 min';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-rejection of pending appointments...');

        $cutoffTime = Carbon::now()->subMinutes(5);
        $pendingAppointments = Appointment::where('appointment_status', 'Pending')
            ->where('created_at', '<', $cutoffTime)
            ->get();

        $rejectedCount = 0;

        foreach ($pendingAppointments as $appointment) {
            $this->info("Rejecting appointment ID: {$appointment->id} (Created: {$appointment->created_at})");
            $appointment->update(['appointment_status' => 'Declined']);
            $rejectedCount++;
        }

        $this->info("Auto-rejection completed.");
        $this->info("Rejected: {$rejectedCount} appointments");
    }
}
