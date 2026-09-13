<?php

namespace App\Http\Controllers;

use App\Enums\Device;
use App\Enums\Metric;
use App\Models\DailyMetric;
use App\Models\Website;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ListWebsitesController
{
    public function __invoke(): View
    {
        $websites = Website::query()->orderBy('name')->get();

        if ($websites->isEmpty()) {
            return view('websites.index', [
                'websites' => $websites,
                'websiteStats' => collect(),
            ]);
        }

        /** @var Collection<string, CarbonImmutable> $todayByWebsite */
        $todayByWebsite = $websites->mapWithKeys(fn (Website $website): array => [
            $website->getKey() => CarbonImmutable::now($website->timezone)->startOfDay(),
        ]);

        $from = $todayByWebsite
            ->map(fn (CarbonImmutable $today): string => $today->subDays(14)->toDateString())
            ->min();
        $to = $todayByWebsite
            ->map(fn (CarbonImmutable $today): string => $today->toDateString())
            ->max();

        /** @var Collection<string, Collection<int, DailyMetric>> $dailyMetrics */
        $dailyMetrics = DailyMetric::query()
            ->whereIn('website_uuid', $websites->modelKeys())
            ->whereBetween('date', [$from, $to])
            ->where(function (Builder $query): void {
                $query
                    ->where('metric', Metric::Path->value)
                    ->orWhere(function (Builder $query): void {
                        $query
                            ->where('metric', Metric::Device->value)
                            ->where('value', Device::Bot->value);
                    });
            })
            ->selectRaw('website_uuid, date, metric, SUM(count) as count')
            ->groupBy('website_uuid', 'date', 'metric')
            ->get()
            ->groupBy('website_uuid');

        $websiteStats = $websites->mapWithKeys(function (Website $website) use ($dailyMetrics, $todayByWebsite): array {
            $today = $todayByWebsite->get($website->getKey());
            $metrics = $dailyMetrics->get($website->getKey(), collect());
            $isTrackingDevice = $website->isTracking(Metric::Device);

            $pathRequests = $metrics
                ->where('metric', Metric::Path->value)
                ->mapWithKeys(fn (DailyMetric $metric): array => [
                    (string) $metric->getRawOriginal('date') => $metric->count,
                ]);
            $botRequests = $isTrackingDevice
                ? $metrics
                    ->where('metric', Metric::Device->value)
                    ->mapWithKeys(fn (DailyMetric $metric): array => [
                        (string) $metric->getRawOriginal('date') => $metric->count,
                    ])
                : collect();

            $requests = function (CarbonImmutable $date) use ($pathRequests, $botRequests): int {
                $key = $date->toDateString();

                return max(0, (int) $pathRequests->get($key, 0) - (int) $botRequests->get($key, 0));
            };

            $sparkline = collect(range(6, 0))
                ->map(fn (int $offset): int => $requests($today->subDays($offset)))
                ->all();

            $currentSevenDays = collect(range(7, 1))
                ->sum(fn (int $offset): int => $requests($today->subDays($offset)));
            $previousSevenDays = collect(range(14, 8))
                ->sum(fn (int $offset): int => $requests($today->subDays($offset)));
            $difference = $currentSevenDays - $previousSevenDays;
            $canIdentifyHumanTraffic = ! $website->should_track_bots || $isTrackingDevice;

            return [
                $website->getKey() => [
                    'today' => $requests($today),
                    'traffic_label' => $canIdentifyHumanTraffic ? 'human today' : 'requests today',
                    'sparkline' => $sparkline,
                    'trend_direction' => match (true) {
                        $difference > 0 => 'up',
                        $difference < 0 => 'down',
                        default => 'flat',
                    },
                    'trend_percentage' => $previousSevenDays > 0
                        ? (int) round(abs($difference) / $previousSevenDays * 100)
                        : null,
                ],
            ];
        });

        return view('websites.index', [
            'websites' => $websites,
            'websiteStats' => $websiteStats,
        ]);
    }
}
