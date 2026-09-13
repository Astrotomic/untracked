<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CollectRawMetricsController
{
    public function __invoke(Request $request, Website $website): Response
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'referrer' => ['nullable', 'string', 'max:2048'],
        ]);

        $website->recordRaw(
            url: $validated['url'],
            ip: (string) $request->ip(),
            userAgent: (string) $request->userAgent(),
            referrer: $validated['referrer'] ?? null,
        );

        return response()->noContent();
    }
}
