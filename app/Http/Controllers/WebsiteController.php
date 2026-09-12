<?php

namespace App\Http\Controllers;

use App\Enums\Device;
use App\Enums\Metric;
use App\Http\Requests\WebsiteRequest;
use App\Models\DailyMetric;
use App\Models\Website;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $websiteStats = collect();

        if ($websites->isNotEmpty()) {
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

                $pathRequests = $metrics
                    ->where('metric', Metric::Path->value)
                    ->mapWithKeys(fn (DailyMetric $metric): array => [
                        (string) $metric->date => $metric->count,
                    ]);
                $botRequests = $metrics
                    ->where('metric', Metric::Device->value)
                    ->mapWithKeys(fn (DailyMetric $metric): array => [
                        (string) $metric->date => $metric->count,
                    ]);

                $humanRequests = function (CarbonImmutable $date) use ($pathRequests, $botRequests): int {
                    $key = $date->toDateString();

                    return max(0, (int) $pathRequests->get($key, 0) - (int) $botRequests->get($key, 0));
                };

                $sparkline = collect(range(6, 0))
                    ->map(fn (int $offset): int => $humanRequests($today->subDays($offset)))
                    ->all();

                $currentSevenDays = collect(range(7, 1))
                    ->sum(fn (int $offset): int => $humanRequests($today->subDays($offset)));
                $previousSevenDays = collect(range(14, 8))
                    ->sum(fn (int $offset): int => $humanRequests($today->subDays($offset)));
                $difference = $currentSevenDays - $previousSevenDays;

                return [
                    $website->getKey() => [
                        'today' => $humanRequests($today),
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
        }

        return view('websites.index', compact('websites', 'websiteStats'));
    }

    public function create(): View
    {
        return view('websites.create');
    }

    public function store(WebsiteRequest $request): RedirectResponse
    {
        $website = Website::query()->create($request->validated());

        return redirect()->route('websites.show', $website);
    }

    public function show(Request $request, Website $website): View
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
            ->map(function (int $offset) use ($dailyRequests, $dailyBotRequests, $from): array {
                $date = $from->copy()->addDays($offset);
                $requests = (int) $dailyRequests->get($date->toDateString(), 0);
                $bots = (int) $dailyBotRequests->get($date->toDateString(), 0);

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->format('M j'),
                    'human' => max(0, $requests - $bots),
                    'bot' => $bots,
                ];
            });

        $requests = (int) $metrics->get(Metric::Path->value)?->sum(fn (DailyMetric $metric) => $metric->count);
        $pathCount = $metrics->get(Metric::Path->value)?->count() ?? 0;
        $countryCount = $metrics->get(Metric::Country->value)?->count() ?? 0;
        $deviceMetrics = $metrics->get(Metric::Device->value) ?? collect();
        $botRequests = (int) $deviceMetrics
            ->where('value', Device::Bot->value)
            ->sum(fn (DailyMetric $metric) => $metric->count);

        $countryValues = $metrics->get(Metric::Country->value)
            ?->mapWithKeys(fn (DailyMetric $metric): array => [
                $metric->value => ['requests' => (int) $metric->count],
            ])
            ->all() ?? [];

        return view('websites.show', compact(
            'website',
            'metrics',
            'days',
            'requests',
            'pathCount',
            'countryCount',
            'botRequests',
            'trend',
            'countryValues',
        ));
    }

    public function edit(Website $website): View
    {
        return view('websites.edit', compact('website'));
    }

    public function update(WebsiteRequest $request, Website $website): RedirectResponse
    {
        $website->update($request->validated());

        return redirect()->route('websites.show', $website);
    }

    public function destroy(Website $website): RedirectResponse
    {
        $website->delete();

        return redirect()->route('websites.index');
    }
}
