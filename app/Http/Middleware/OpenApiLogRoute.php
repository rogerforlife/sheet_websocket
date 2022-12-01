<?php

namespace App\Http\Middleware;

use Closure;
use Log;
use Route;
use Str;

class OpenApiLogRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $log = [
            'CLASS' => class_basename(Route::current()->controller),
            'FUNCTION' => Route::current()->action['controller'],
            'IP' => $request->ip(),
            // 'TOKEN' => $request->header()['x-authorization'][0],
            'URL' => $request->url(),
            'METHOD' => $request->getMethod(),
            'REQUEST_DATA' => count($request->all()) === 0 ? null : json_encode($request->all()),
            'STATUS_CODE' => $response->status(),
            'RESPONSE' => Str::substr($response->getContent(), 0, 1000),
        ];

        Log::info(json_encode($log));
        Log::channel('fluent-api')->info($response->status(), $log);

        return $response;
    }
}
