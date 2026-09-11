<?php

namespace App\Http\Controllers;

use App\Analytics\Metric;
use App\Http\Requests\WebsiteRequest;
use App\Models\DailyMetric;
use App\Models\Website;
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

        $from = now($website->timezone)->startOfDay()->subDays($days - 1)->toDateString();

        /** @var Collection<string, Collection<int, DailyMetric>> $metrics */
        $metrics = DailyMetric::query()
            ->where('website_id', $website->getKey())
            ->where('date', '>=', $from)
            ->selectRaw('metric, value, SUM(count) as count')
            ->groupBy('metric', 'value')
            ->orderByDesc('count')
            ->get()
            ->groupBy('metric');

        $requests = (int) ($metrics->get(Metric::Path->value)?->sum('count') ?? 0);

        return view('websites.show', compact('website', 'metrics', 'days', 'requests'));
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
