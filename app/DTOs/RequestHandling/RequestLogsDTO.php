<?php

namespace App\DTOs\RequestHandling;

use App\DTOs\BaseDTO;

class RequestLogsDTO extends BaseDTO
{
    public mixed $ip_address;
    public mixed $user_agent;
    public mixed $request_url;
    public mixed $request_method;
    public mixed $request_body;

    public function __construct($request, $requestData)
    {
        $this->ip_address = $request->ip();
        $this->user_agent = $request->userAgent();
        $this->request_url = $request->url();
        $this->request_method = $request->method();
        $this->request_body = json_encode($requestData, true);
    }
}
