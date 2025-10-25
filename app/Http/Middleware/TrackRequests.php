<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\RequestTracker;
use Symfony\Component\HttpFoundation\Response;

class TrackRequests
{
    public function __construct(protected RequestTracker $requestTracker){}


    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->requestTracker->trackRequest($request);

        return $next($request);
    }
}
