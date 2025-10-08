<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeNativePhpRequest
{
    /**
     * Remove Inertia/Ajax headers for requests originating from the NativePHP runtime
     * so the server returns full HTML instead of raw Inertia JSON.
     */
    public function handle(Request $request, Closure $next)
    {
        if(config('nativephp.app_id')!=null){
            // Remove headers that make the app return JSON payloads
            $request->headers->remove('X-Inertia');
            $request->headers->remove('X-Inertia-Version');
            $request->headers->remove('X-Requested-With');

            // Prefer HTML response
            $request->headers->set('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        }

        return $next($request);
    }
}