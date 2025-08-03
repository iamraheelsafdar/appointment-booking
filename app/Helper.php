<?php

namespace App;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;
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
        return response()->view('error', ['error' => $e], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param $request
     * @param $e
     * @param $functionName
     * @return \Illuminate\Http\JsonResponse
     */
    public static function jsonErrorHandling($request, $e, $functionName): \Illuminate\Http\JsonResponse
    {
        ErrorLog::create((new ErrorLogsDTO($request, $e, $functionName))->toArray());
        return response()->json(['error' => 'Something went wrong please try again'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
    }
}
