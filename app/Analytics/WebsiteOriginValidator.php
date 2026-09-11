<?php

namespace App\Analytics;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class WebsiteOriginValidator
{
    public function validate(Request $request, Website $website): void
    {
        $origin = $request->header('Origin');

        if (! $origin) {
            return;
        }

        $host = parse_url($origin, PHP_URL_HOST);

        abort_unless(
            is_string($host) && hash_equals(Str::lower($website->domain), Str::lower($host)),
            Response::HTTP_FORBIDDEN,
        );
    }
}
