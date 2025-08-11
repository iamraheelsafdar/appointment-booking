<?php

namespace App\DTOs\Transaction;

use App\DTOs\BaseDTO;

class CreateTransactionDTO extends BaseDTO
{

    public mixed $transaction_id;
    public mixed $session_id;
    public mixed $amount;
    public mixed $type;
    public mixed $currency;
    public mixed $description;
    public mixed $status;
    public mixed $transaction_expiration_date;
    public mixed $appointment_id;

    public function __construct($trxId, $amount, $type, $currency, $description, $sessionId, $expiresAtFormatted, $appointmentId)
    {
        $this->transaction_id = $trxId;
        $this->session_id = $sessionId;
        $this->amount = $amount;
        $this->type = $type;
        $this->currency = $currency;
        $this->description = $description;
        $this->status = 'Processing';
        $this->transaction_expiration_date = $expiresAtFormatted;
        $this->appointment_id = $appointmentId;
    }
}
