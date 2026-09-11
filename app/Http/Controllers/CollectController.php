<?php

namespace App\Http\Controllers;

use App\Analytics\MetricRecorder;
use App\Analytics\RequestNormalizer;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CollectController extends Controller
{
    public function __invoke(
        Request $request,
        Website $website,
        RequestNormalizer $normalizer,
        MetricRecorder $recorder,
    ): Response {
        $this->validateOrigin($request, $website);

        $request->validate([
            'path' => ['required', 'string', 'max:2048'],
            'country' => ['nullable', 'string', 'max:2'],
            'browser' => ['nullable', 'string', 'max:64'],
            'os' => ['nullable', 'string', 'max:64'],
            'device' => ['nullable', 'string', 'max:32'],
            'format' => ['nullable', 'string', 'max:64'],
            'bot' => ['nullable', 'boolean'],
        ]);

        $dimensions = $normalizer->normalize($request);

        if ($dimensions['is_bot'] && ! $website->track_bots) {
            return response()->noContent();
        }

        unset($dimensions['is_bot']);

        $recorder->record($website, $dimensions);

        return response()->noContent();
    }

    private function validateOrigin(Request $request, Website $website): void
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
