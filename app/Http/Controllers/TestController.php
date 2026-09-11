<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $website = Website::query()->firstOrCreate(
            ['domain' => $request->getHost()],
            [
                'name' => 'Untracked Test',
                'timezone' => config('app.timezone'),
                'should_track_bots' => true,
            ],
        );

        $dimensions = $website->recordRaw(
            url: $request->fullUrl(),
            ip: (string) $request->ip(),
            userAgent: (string) $request->userAgent(),
            referrer: $request->headers->get('referer'),
        );

        return response()->json($dimensions);
    }
}
