<?php

namespace App\Http\Middleware;

use App\Models\Website;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ValidateWebsiteOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');

        if (! $origin) {
            return $next($request);
        }

        /** @var Website $website */
        $website = $request->route('website');
        $host = parse_url($origin, PHP_URL_HOST);

        abort_unless(
            is_string($host) && hash_equals(Str::lower($website->domain), Str::lower($host)),
            Response::HTTP_FORBIDDEN,
        );

        return $next($request);
    }
}
