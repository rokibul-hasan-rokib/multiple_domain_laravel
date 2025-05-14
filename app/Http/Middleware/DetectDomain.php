<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DetectDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Set the domain in config for later use
        config(['app.current_domain' => $host]);

        // Share domain info globally to all views (optional)
        View::share('current_domain', $host);

        if ($host == parse_url(config('app.domains.domain1'), PHP_URL_HOST)) {
            // You can bind services or configs here
            config(['app.theme' => 'theme1']);
        }

        if ($host == parse_url(config('app.domains.domain2'), PHP_URL_HOST)) {
            config(['app.theme' => 'theme2']);
        }

        if ($host == parse_url(config('app.domains.domain3'), PHP_URL_HOST)) {
            config(['app.theme' => 'theme3']);
        }

        return $next($request);
    }
}
