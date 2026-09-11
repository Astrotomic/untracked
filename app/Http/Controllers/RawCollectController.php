<?php

namespace App\Http\Controllers;

use App\Analytics\Dimensions;
use App\Analytics\Enums\Format;
use App\Analytics\IpManager;
use App\Analytics\MetricRecorder;
use App\Analytics\Normalizers\AttributionValueNormalizer;
use App\Analytics\Normalizers\ReferrerNormalizer;
use App\Analytics\PathNormalizer;
use App\Analytics\UserAgentManager;
use App\Analytics\WebsiteOriginValidator;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RawCollectController extends Controller
{
    public function __invoke(
        Request $request,
        Website $website,
        WebsiteOriginValidator $originValidator,
        PathNormalizer $pathNormalizer,
        ReferrerNormalizer $referrers,
        AttributionValueNormalizer $attributionValues,
        UserAgentManager $userAgents,
        IpManager $ips,
        MetricRecorder $recorder,
    ): Response {
        $originValidator->validate($request, $website);

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

        $userAgent = $userAgents->driver()->resolve((string) $request->userAgent());

        if ($userAgent->isBot() && ! $website->should_track_bots) {
            return response()->noContent();
        }

        $recorder->record($website, new Dimensions(
            path: $pathNormalizer->normalize($validated['path']),
            country: $ips->driver()->country((string) $request->ip()),
            browser: $userAgent->browser,
            os: $userAgent->os,
            device: $userAgent->device,
            format: Format::normalize((string) ($validated['format'] ?? 'html')),
            referrer: $referrers->normalize($validated['referrer'] ?? null, $website->domain),
            utmSource: $attributionValues->normalize($validated['utm_source'] ?? null),
            utmMedium: $attributionValues->normalize($validated['utm_medium'] ?? null),
            utmCampaign: $attributionValues->normalize($validated['utm_campaign'] ?? null),
            utmTerm: $attributionValues->normalize($validated['utm_term'] ?? null),
            utmContent: $attributionValues->normalize($validated['utm_content'] ?? null),
        ));

        return response()->noContent();
    }
}
