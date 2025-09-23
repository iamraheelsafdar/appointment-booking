<?php

namespace App\Jobs;

use App\Mail\Appointment\AppointmentCancellationMail;
use App\Mail\Appointment\AppointmentCancellationReceivingMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class AppointmentCancellationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $customerName;
    public $customerEmail;
    public $bookingDetails;
    public $coachName;
    public $coachEmail;
    public $adminName;
    public $adminEmail;
    public $cancellationReason;

    /**
     * Create a new job instance.
     */
    public function __construct($customerName, $customerEmail, $bookingDetails, $cancellationReason = 'Appointment has been cancelled')
    {
        $this->customerName = $customerName;
        $this->customerEmail = $customerEmail;
        $this->bookingDetails = $bookingDetails;
        $this->cancellationReason = $cancellationReason;
        $this->onQueue('reject-booking');
    }

    /**
     * Set coach information for notifications
     */
    public function setCoachInfo($coachName, $coachEmail)
    {
        $this->coachName = $coachName;
        $this->coachEmail = $coachEmail;
        return $this;
    }

    /**
     * Set admin information for notifications
     */
    public function setAdminInfo($adminName, $adminEmail)
    {
        $this->adminName = $adminName;
        $this->adminEmail = $adminEmail;
        return $this;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Send cancellation email to customer (only one email to customer)
            if ($this->customerEmail) {
                Mail::to($this->customerEmail)->send(
                    new AppointmentCancellationMail($this->customerName, $this->bookingDetails)
                );
                \Log::info("Cancellation email sent to customer: {$this->customerEmail}");
            }

            // Send notification to coach (only if coach exists)
            if ($this->coachEmail && $this->coachName) {
                Mail::to($this->coachEmail)->send(
                    new AppointmentCancellationReceivingMail($this->coachName, $this->bookingDetails, 'coach')
                );
                \Log::info("Cancellation notification sent to coach: {$this->coachEmail}");
            }

            // Send notification to admin (only if admin exists)
            if ($this->adminEmail && $this->adminName) {
                Mail::to($this->adminEmail)->send(
                    new AppointmentCancellationReceivingMail($this->adminName, $this->bookingDetails, 'admin')
                );
                \Log::info("Cancellation notification sent to admin: {$this->adminEmail}");
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send cancellation emails: ' . $e->getMessage());
            throw $e;
        }
    }
}
