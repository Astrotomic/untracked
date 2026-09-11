<?php

namespace App\Http\Controllers;

use App\Enums\Device;
use App\Enums\Format;
use App\Models\Website;
use App\Values\Dimensions;
use App\Values\UserAgent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ProcessedCollectController extends Controller
{
    public function __invoke(Request $request, Website $website): Response
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'max:2048'],
            'country' => ['nullable', 'string', 'regex:/^[A-Za-z]{2}$/'],
            'browser' => ['required', 'string', 'max:100'],
            'os' => ['required', 'string', 'max:100'],
            'device' => ['required', Rule::enum(Device::class)],
            'format' => ['required', Rule::enum(Format::class)],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'utm_source' => ['nullable', 'string', 'max:500'],
            'utm_medium' => ['nullable', 'string', 'max:500'],
            'utm_campaign' => ['nullable', 'string', 'max:500'],
            'utm_term' => ['nullable', 'string', 'max:500'],
            'utm_content' => ['nullable', 'string', 'max:500'],
        ]);

        $userAgent = UserAgent::from(
            browser: $validated['browser'],
            os: $validated['os'],
            device: $validated['device'],
        );

        if ($userAgent->isBot() && ! $website->should_track_bots) {
            return response()->noContent();
        }

        $website->record(Dimensions::from(
            path: $validated['path'],
            country: $validated['country'] ?? null,
            userAgent: $userAgent,
            format: Format::from($validated['format']),
            referrer: $validated['referrer'] ?? null,
            utmSource: $validated['utm_source'] ?? null,
            utmMedium: $validated['utm_medium'] ?? null,
            utmCampaign: $validated['utm_campaign'] ?? null,
            utmTerm: $validated['utm_term'] ?? null,
            utmContent: $validated['utm_content'] ?? null,
            website: $website,
        ));

        return response()->noContent();
    }
}
