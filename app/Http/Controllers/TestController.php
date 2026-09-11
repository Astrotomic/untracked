<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TestController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $website = Website::query()->firstOrCreate(
            ['domain' => $request->getHost()],
            [
                'name' => 'Untracked Test',
                'timezone' => config('app.timezone'),
                'should_track_bots' => true,
            ],
        );

        $website->recordRaw(
            url: $request->fullUrl(),
            ip: (string) $request->ip(),
            userAgent: (string) $request->userAgent(),
            referrer: $request->headers->get('referer'),
        );

        return response('Tracked. Refresh to add another request.');
    }
}
