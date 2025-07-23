<?php

namespace App\Http\Middleware;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Closure;

class SecureHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */

    private array $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
        'server',
    ];
    public function handle($request, Closure $next)
    {
        $this->removeUnwantedHeaders($this->unwantedHeaderList);
        $response = $next($request);
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Permissions-Policy', 'autoplay=(self), camera=(), encrypted-media=(self), fullscreen=(), geolocation=(self), gyroscope=(self), magnetometer=(), microphone=(), midi=(), payment=(), sync-xhr=(self), usb=()');
        //        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' platform.twitter.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' * data:; font-src 'self' data: ; connect-src 'self'; media-src 'self'; frame-src 'self' platform.twitter.com github.com *.youtube.com *.vimeo.com; object-src 'none'; base-uri 'self'; report-uri ");

        return $response;
    }


    /**
     * @param $headerList
     * @return void
     */
    private function removeUnwantedHeaders($headerList): void
    {
        foreach ($headerList as $header)
            header_remove($header);
    }
}
