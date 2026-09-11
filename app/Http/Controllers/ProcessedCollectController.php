<?php

namespace App\Http\Controllers;

use App\Analytics\Dimensions;
use App\Analytics\Enums\Device;
use App\Analytics\Enums\Format;
use App\Analytics\MetricRecorder;
use App\Analytics\Normalizers\BrowserNormalizer;
use App\Analytics\Normalizers\OperatingSystemNormalizer;
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
        BrowserNormalizer $browsers,
        OperatingSystemNormalizer $operatingSystems,
        MetricRecorder $recorder,
    ): Response {
        $originValidator->validate($request, $website);

        $validated = $request->validate([
            'path' => ['required', 'string', 'max:2048'],
            'country' => ['required', 'string', 'regex:/^[A-Za-z]{2}$/'],
            'browser' => ['required', 'string', 'max:100'],
            'os' => ['required', 'string', 'max:100'],
            'device' => ['required', Rule::enum(Device::class)],
            'format' => ['required', Rule::enum(Format::class)],
        ]);

        $device = Device::from($validated['device']);
        $browser = $browsers->normalize($validated['browser']);
        $os = $operatingSystems->normalize($validated['os']);

        if ($device === Device::Bot) {
            $browser = 'Bot';
            $os = 'Bot';
        }

        $dimensions = new Dimensions(
            path: $pathNormalizer->normalize($validated['path']),
            country: strtoupper($validated['country']),
            browser: $browser,
            os: $os,
            device: $device,
            format: Format::from($validated['format']),
        );

        if ($dimensions->isBot() && ! $website->should_track_bots) {
            return response()->noContent();
        }

        $recorder->record($website, $dimensions);

        return response()->noContent();
    }
}
