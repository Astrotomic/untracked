<x-layouts.app :title="$website->name">
    @php
        $summary = [
            ['label' => 'Requests', 'value' => $requests, 'icon' => 'activity'],
            ['label' => 'Paths', 'value' => $pathCount, 'icon' => 'file-text'],
            ['label' => 'Countries', 'value' => $countryCount, 'icon' => 'globe-2'],
            ['label' => 'Bot requests', 'value' => $botRequests, 'icon' => 'bot'],
        ];

        $breakdowns = [
            'path' => ['label' => 'Paths', 'icon' => 'file-text'],
            'client' => ['label' => 'Clients', 'icon' => 'monitor'],
            'os' => ['label' => 'Operating systems', 'icon' => 'laptop'],
            'device' => ['label' => 'Devices', 'icon' => 'smartphone'],
            'format' => ['label' => 'Formats', 'icon' => 'files'],
            'referrer' => ['label' => 'Referrers', 'icon' => 'link-2'],
            'utm_source' => ['label' => 'UTM sources', 'icon' => 'megaphone'],
            'utm_medium' => ['label' => 'UTM mediums', 'icon' => 'send'],
            'utm_campaign' => ['label' => 'UTM campaigns', 'icon' => 'target'],
            'utm_term' => ['label' => 'UTM terms', 'icon' => 'search'],
            'utm_content' => ['label' => 'UTM contents', 'icon' => 'panels-top-left'],
        ];

        $countries = $metrics->get('country') ?? collect();
        $countryTotal = max(1, (int) $countries->sum('count'));
        $countryName = static fn (string $country): string => \Locale::getDisplayRegion('und_'.strtoupper($country), 'en') ?: strtoupper($country);
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-6">
        <div>
            <a href="{{ route('websites.index') }}" class="text-sm text-zinc-500 hover:text-zinc-300">← Websites</a>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-semibold tracking-tight">{{ $website->name }}</h1>
                <span class="rounded-full border border-zinc-800 bg-zinc-900 px-2.5 py-1 text-xs text-zinc-400">{{ $website->should_track_bots ? 'Bots included' : 'Bots rejected' }}</span>
            </div>
            <p class="mt-1 text-zinc-500">{{ $website->domain }} · {{ $website->timezone }}</p>
        </div>

        <a href="{{ route('websites.edit', $website) }}" class="rounded-lg border border-zinc-700 px-4 py-2 text-sm text-zinc-300 transition hover:border-zinc-500 hover:text-white">Settings</a>
    </div>

    <div class="mt-8 flex flex-wrap items-center gap-2">
        @foreach ([7, 30, 90, 365] as $range)
            <a href="{{ route('websites.show', [$website, 'days' => $range]) }}" class="rounded-full px-3 py-1.5 text-sm transition {{ $days === $range ? 'bg-white text-zinc-950' : 'bg-zinc-900 text-zinc-400 hover:text-white' }}">{{ $range }} days</a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($summary as $item)
            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm text-zinc-500">{{ $item['label'] }}</p>
                    <span class="rounded-lg bg-zinc-800/80 p-2 text-zinc-400">
                        <x-icon.lucide :name="$item['icon']" />
                    </span>
                </div>
                <p class="mt-5 text-3xl font-semibold tabular-nums tracking-tight">{{ number_format($item['value']) }}</p>
            </section>
        @endforeach
    </div>

    <section class="mt-6 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-zinc-100">Requests over time</p>
                <p class="mt-1 text-sm text-zinc-500">Daily request counters for the selected period.</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-semibold tabular-nums">{{ number_format($requests) }}</p>
                <p class="text-xs text-zinc-500">{{ $days }} day total</p>
            </div>
        </div>
        <div class="mt-6 h-72">
            <canvas id="requests-chart"></canvas>
        </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="rounded-lg bg-zinc-800/80 p-2 text-zinc-400"><x-icon.lucide name="globe-2" /></span>
                <div>
                    <h2 class="font-medium">Countries</h2>
                    <p class="text-sm text-zinc-500">Request distribution by resolved country.</p>
                </div>
            </div>
            <div id="country-map" class="mt-6 min-h-80"></div>
        </section>

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-medium">Top countries</h2>
                <span class="text-xs tabular-nums text-zinc-500">{{ number_format((int) $countries->sum('count')) }} resolved</span>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($countries->take(10) as $row)
                    @php
                        $percentage = ($row->count / $countryTotal) * 100;
                    @endphp
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-4 text-sm">
                            <span class="flex min-w-0 items-center gap-2.5 font-medium text-zinc-300">
                                <x-icon.country :country="$row->value" />
                                <span class="truncate" title="{{ $row->value }}">{{ $countryName($row->value) }}</span>
                            </span>
                            <span class="shrink-0 tabular-nums text-zinc-500">{{ number_format($row->count) }} · {{ number_format($percentage, 1) }}%</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-zinc-800">
                            <div class="h-full rounded-full bg-zinc-300" style="width: {{ min(100, $percentage) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="flex min-h-48 items-center justify-center text-sm text-zinc-600">No country data yet.</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @foreach ($breakdowns as $metric => $config)
            @php
                $rows = $metrics->get($metric) ?? collect();
                $total = max(1, (int) $rows->sum('count'));
            @endphp
            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="rounded-lg bg-zinc-800/80 p-2 text-zinc-400"><x-icon.lucide :name="$config['icon']" /></span>
                        <h2 class="font-medium">{{ $config['label'] }}</h2>
                    </div>
                    <span class="text-xs tabular-nums text-zinc-600">{{ number_format((int) $rows->sum('count')) }}</span>
                </div>

                <div class="mt-5 space-y-2.5">
                    @forelse ($rows->take(8) as $row)
                        @php
                            $percentage = ($row->count / $total) * 100;
                        @endphp
                        <div class="relative overflow-hidden rounded-lg bg-zinc-950/50">
                            <div class="absolute inset-y-0 left-0 bg-zinc-800/60" style="width: {{ min(100, $percentage) }}%"></div>
                            <div class="relative flex items-center justify-between gap-4 px-3 py-2.5 text-sm">
                                <span class="flex min-w-0 items-center gap-2.5 text-zinc-300" title="{{ $row->value }}">
                                    @if ($metric === 'client')
                                        <x-icon.client :client="$row->value" />
                                    @elseif ($metric === 'referrer')
                                        <x-icon.favicon :domain="$row->value" />
                                    @endif
                                    <span class="truncate">{{ $row->value }}</span>
                                </span>
                                <span class="shrink-0 tabular-nums text-zinc-500">{{ number_format($row->count) }} <span class="text-zinc-700">·</span> {{ number_format($percentage, 1) }}%</span>
                            </div>
                        </div>
                    @empty
                        <div class="flex min-h-36 items-center justify-center text-sm text-zinc-600">No data yet.</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    <details class="mt-8 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-5">
        <summary class="cursor-pointer font-medium text-zinc-300">Collection setup</summary>
        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <section>
                <h2 class="font-medium">Browser collection</h2>
                <p class="mt-2 text-sm leading-6 text-zinc-400">The script only sends the full page URL and referrer. Untracked derives path, UTM values, country, client, OS and device in memory; the raw URL, IP, User-Agent and referrer are never persisted.</p>
                <pre class="mt-4 overflow-x-auto rounded-lg bg-black/40 p-4 text-sm text-zinc-300"><code>&lt;script defer data-website-id="{{ $website->uuid }}" src="{{ url('/script.js') }}"&gt;&lt;/script&gt;</code></pre>
            </section>

            <section>
                <h2 class="font-medium">Server-side collection</h2>
                <p class="mt-2 text-sm leading-6 text-zinc-400">Send only the coarse values you actually want to keep. Bot dimensions are reduced with the same rules as raw collection.</p>
                <pre class="mt-4 overflow-x-auto rounded-lg bg-black/40 p-4 text-sm text-zinc-300"><code>POST {{ route('collect.processed', $website) }}

{
  "path": "/blog/example",
  "country": "DE",
  "client": "Firefox",
  "os": "Linux",
  "device": "desktop",
  "format": "markdown"
}</code></pre>
            </section>
        </div>
    </details>

    <script type="application/json" id="analytics-dashboard-data">@json([
        'trend' => $trend,
        'countries' => $countryValues,
    ])</script>
</x-layouts.app>
