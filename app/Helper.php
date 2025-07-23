<?php

namespace App;

use App\DTOs\RequestHandling\ErrorLogsDTO;
use Illuminate\Http\Response;
use App\Models\ErrorLog;

class Helper
{
    /**
     * @param $request
     * @param $e
     * @param $functionName
     * @return Response
     */
    public static function errorHandling($request, $e, $functionName): Response
    {
        ErrorLog::create((new ErrorLogsDTO($request, $e, $functionName))->toArray());
        return response()->view('error', ['error' => $e], 500);
    }
}
