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
        $dailyRequests = (clone $query)
            ->where('metric', Metric::Path->value)
            ->selectRaw('date, SUM(count) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        /** @var Collection<string, int|string> $dailyBotRequests */
        $dailyBotRequests = (clone $query)
            ->where('metric', Metric::Device->value)
            ->where('value', Device::Bot->value)
            ->selectRaw('date, SUM(count) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $trend = collect(range(0, $days - 1))
            ->map(function (int $offset) use ($dailyRequests, $dailyBotRequests, $from, $website): array {
                $date = $from->copy()->addDays($offset);
                $requests = (int) $dailyRequests->get($date->toDateString(), 0);
                $bots = (int) $dailyBotRequests->get($date->toDateString(), 0);
                $point = [
                    'date' => $date->toDateString(),
                    'label' => $date->format('M j'),
                    'human' => max(0, $requests - $bots),
                ];

                if ($website->should_track_bots) {
                    $point['bot'] = $bots;
                }

                return $point;
            });

        $requests = (int) $metrics->get(Metric::Path->value)?->sum(fn (DailyMetric $metric) => $metric->count);
        $pathCount = $metrics->get(Metric::Path->value)?->count() ?? 0;
        $countryCount = $metrics->get(Metric::Country->value)?->count() ?? 0;
        $deviceMetrics = $metrics->get(Metric::Device->value) ?? collect();
        $botRequests = (int) $deviceMetrics
            ->where('value', Device::Bot->value)
            ->sum(fn (DailyMetric $metric) => $metric->count);

        if (! $website->should_track_bots) {
            $requests = max(0, $requests - $botRequests);
        }

        $countryValues = $metrics->get(Metric::Country->value)
            ?->mapWithKeys(fn (DailyMetric $metric): array => [
                $metric->value => ['requests' => (int) $metric->count],
            ])
            ->all() ?? [];

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
        ]);
    }
}
