<?php

namespace App\Http\Controllers;

use App\Enums\Format;
use App\Managers\IpManager;
use App\Managers\UserAgentManager;
use App\Models\Website;
use App\Values\Dimensions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RawCollectController extends Controller
{
    public function __invoke(Request $request, Website $website): Response
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'max:2048'],
            'format' => ['nullable', 'string', 'max:64'],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'utm_source' => ['nullable', 'string', 'max:500'],
            'utm_medium' => ['nullable', 'string', 'max:500'],
            'utm_campaign' => ['nullable', 'string', 'max:500'],
            'utm_term' => ['nullable', 'string', 'max:500'],
            'utm_content' => ['nullable', 'string', 'max:500'],
        ]);

        $userAgent = UserAgentManager::make()->driver()->resolve((string) $request->userAgent());

        if ($userAgent->isBot() && ! $website->should_track_bots) {
            return response()->noContent();
        }

        $website->record(Dimensions::from(
            path: $validated['path'],
            country: IpManager::make()->driver()->country((string) $request->ip()),
            userAgent: $userAgent,
            format: Format::normalize((string) ($validated['format'] ?? 'html')),
            referrer: $validated['referrer'] ?? null,
            utmSource: $validated['utm_source'] ?? null,
            utmMedium: $validated['utm_medium'] ?? null,
            utmCampaign: $validated['utm_campaign'] ?? null,
            utmTerm: $validated['utm_term'] ?? null,
            utmContent: $validated['utm_content'] ?? null,
            website: $website
        ));

        return response()->noContent();
    }
}
