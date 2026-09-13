<?php

namespace App\Http\Controllers;

use App\Enums\Device;
use App\Enums\Metric;
use App\Models\DailyMetric;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ShowWebsiteController
{
    public function __invoke(Request $request, Website $website): View
    {
        $days = in_array((int) $request->integer('days', 30), [7, 30, 90, 365], true)
            ? (int) $request->integer('days', 30)
            : 30;

        $today = now($website->timezone)->startOfDay();
        $from = $today->copy()->subDays($days - 1);
        $isTrackingPath = $website->isTracking(Metric::Path);
        $isTrackingCountry = $website->isTracking(Metric::Country);
        $isTrackingDevice = $website->isTracking(Metric::Device);
        $showsBotTraffic = $website->should_track_bots && $isTrackingDevice;
        $showsHumanTraffic = ! $website->should_track_bots || $isTrackingDevice;

        $query = DailyMetric::query()
            ->where('website_uuid', $website->getKey())
            ->whereBetween('date', [$from->toDateString(), $today->toDateString()]);

        /** @var Collection<string, Collection<int, DailyMetric>> $metrics */
        $metrics = (clone $query)
            ->selectRaw('metric, value, SUM(count) as count')
            ->groupBy('metric', 'value')
            ->orderByDesc('count')
            ->get()
            ->groupBy('metric');

        /** @var Collection<string, int|string> $dailyRequests */
        $dailyRequests = $isTrackingPath
            ? (clone $query)
                ->where('metric', Metric::Path->value)
                ->selectRaw('date, SUM(count) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
            : collect();

        /** @var Collection<string, int|string> $dailyBotRequests */
        $dailyBotRequests = $isTrackingDevice
            ? (clone $query)
                ->where('metric', Metric::Device->value)
                ->where('value', Device::Bot->value)
                ->selectRaw('date, SUM(count) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
            : collect();

        $trend = collect(range(0, $days - 1))
            ->map(function (int $offset) use ($dailyRequests, $dailyBotRequests, $from, $showsBotTraffic, $showsHumanTraffic): array {
                $date = $from->copy()->addDays($offset);
                $requests = (int) $dailyRequests->get($date->toDateString(), 0);
                $bots = (int) $dailyBotRequests->get($date->toDateString(), 0);
                $point = [
                    'date' => $date->toDateString(),
                    'label' => $date->format('M j'),
                    'human' => $showsHumanTraffic ? max(0, $requests - $bots) : $requests,
                ];

                if ($showsBotTraffic) {
                    $point['bot'] = $bots;
                }

                return $point;
            });

        $requests = $isTrackingPath
            ? (int) $metrics->get(Metric::Path->value)?->sum(fn (DailyMetric $metric) => $metric->count)
            : 0;
        $pathCount = $isTrackingPath ? ($metrics->get(Metric::Path->value)?->count() ?? 0) : 0;
        $countryCount = $isTrackingCountry ? ($metrics->get(Metric::Country->value)?->count() ?? 0) : 0;
        $deviceMetrics = $isTrackingDevice ? ($metrics->get(Metric::Device->value) ?? collect()) : collect();
        $botRequests = (int) $deviceMetrics
            ->where('value', Device::Bot->value)
            ->sum(fn (DailyMetric $metric) => $metric->count);

        if (! $website->should_track_bots && $isTrackingDevice) {
            $requests = max(0, $requests - $botRequests);
        }

        $countryValues = $isTrackingCountry
            ? $metrics->get(Metric::Country->value)
                ?->mapWithKeys(fn (DailyMetric $metric): array => [
                    $metric->value => ['requests' => (int) $metric->count],
                ])
                ->all() ?? []
            : [];

        return view('websites.show', [
            'website' => $website,
            'metrics' => $metrics,
            'days' => $days,
            'requests' => $requests,
            'pathCount' => $pathCount,
            'countryCount' => $countryCount,
            'botRequests' => $botRequests,
            'trend' => $trend,
            'countryValues' => $countryValues,
            'showsBotTraffic' => $showsBotTraffic,
            'trafficLabel' => $showsHumanTraffic ? 'Human' : 'Requests',
        ]);
    }
}
