<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use App\DTOs\RequestHandling\RequestLogsDTO;
use Illuminate\Support\Facades\Route;
use App\Models\ApiRequestLog;
use Illuminate\Http\Request;
use Closure;

class RequestLogsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestData = [
            'headers' => $request->headers->all(),
            'request_body' => $request->all(),
        ];
        $logs = ApiRequestLog::create((new RequestLogsDTO($request, $requestData))->toArray());
        $request['request_id'] = $logs->id;
        if (auth()->user() && (in_array(Route::currentRouteName(), ['loginView', 'login', 'setPasswordView']))) {
            // Redirect authenticated users away from login or forget password pages
            return redirect()->route('dashboard'); // Or any other page you want to redirect to
        }
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
//        ApiRequestLog::find($request['request_id'])->update(['response_body' => $response->getContent()]);
    }
}
