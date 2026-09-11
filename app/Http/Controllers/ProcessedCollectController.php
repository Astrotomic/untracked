<?php

namespace App\Http\Controllers;

use App\Analytics\Dimensions;
use App\Analytics\Enums\Browser;
use App\Analytics\Enums\Device;
use App\Analytics\Enums\Format;
use App\Analytics\Enums\OperatingSystem;
use App\Analytics\MetricRecorder;
use App\Analytics\PathNormalizer;
use App\Analytics\WebsiteOriginValidator;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ProcessedCollectController extends Controller
{
    public function __invoke(
        Request $request,
        Website $website,
        WebsiteOriginValidator $originValidator,
        PathNormalizer $pathNormalizer,
        MetricRecorder $recorder,
    ): Response {
        $originValidator->validate($request, $website);

        $validated = $request->validate([
            'path' => ['required', 'string', 'max:2048'],
            'country' => ['required', 'string', 'regex:/^[A-Za-z]{2}$/'],
            'browser' => ['required', Rule::enum(Browser::class)],
            'os' => ['required', Rule::enum(OperatingSystem::class)],
            'device' => ['required', Rule::enum(Device::class)],
            'format' => ['required', Rule::enum(Format::class)],
        ]);

        $dimensions = new Dimensions(
            path: $pathNormalizer->normalize($validated['path']),
            country: strtoupper($validated['country']),
            browser: Browser::from($validated['browser']),
            os: OperatingSystem::from($validated['os']),
            device: Device::from($validated['device']),
            format: Format::from($validated['format']),
        );

        if ($dimensions->isBot() && ! $website->should_track_bots) {
            return response()->noContent();
        }

        $recorder->record($website, $dimensions);

        return response()->noContent();
    }
}
