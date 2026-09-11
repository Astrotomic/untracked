<?php

namespace App\Http\Controllers;

use App\Enums\Metric;
use App\Http\Requests\WebsiteRequest;
use App\Models\DailyMetric;
use App\Models\Website;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(): View
    {
        return view('websites.index', [
            'websites' => Website::query()->orderBy('name')->get(),
        ]);
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

        /** @var Collection<string, DailyMetric> $dailyRequests */
        $dailyRequests = (clone $query)
            ->where('metric', Metric::Path->value)
            ->selectRaw('date, SUM(count) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn (DailyMetric $metric): string => $metric->date->toDateString());

        $trend = collect(range(0, $days - 1))
            ->map(function (int $offset) use ($dailyRequests, $from): array {
                $date = $from->copy()->addDays($offset);

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->format('M j'),
                    'count' => (int) ($dailyRequests->get($date->toDateString())?->count ?? 0),
                ];
            });

        $requests = (int) $metrics->get(Metric::Path->value)?->sum(fn (DailyMetric $metric) => $metric->count);
        $pathCount = $metrics->get(Metric::Path->value)?->count() ?? 0;
        $countryCount = $metrics->get(Metric::Country->value)?->count() ?? 0;
        $botRequests = (int) ($metrics->get(Metric::Device->value)?->firstWhere('value', 'bot')?->count ?? 0);

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
