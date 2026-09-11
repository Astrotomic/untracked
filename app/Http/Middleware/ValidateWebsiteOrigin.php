<?php

namespace App\Http\Middleware;

use App\Models\Website;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidateWebsiteOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');

        if (empty($origin)) {
            return $next($request);
        }

        $host = parse_url($origin, PHP_URL_HOST);

        if (empty($host)) {
            return $next($request);
        }

        $website = $request->route('website');
        if ($website instanceof Website) {
            if (! Str::equals($website->domain, $host)) {
                throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}
