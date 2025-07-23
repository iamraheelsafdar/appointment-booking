<?php

namespace App\DTOs\RequestHandling;

use App\DTOs\BaseDTO;

class ErrorLogsDTO extends BaseDTO
{
    public mixed $request_id;
    public mixed $error_message;
    public mixed $function_name;
    public mixed $line_number;

    public function __construct($request, $e, $functionName)
    {
        $this->request_id = $request['request_id'];
        $this->error_message = $e->getMessage();
        $this->function_name = $functionName;
        $this->line_number = $e->getLine();

    }
}
