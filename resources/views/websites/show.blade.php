<x-layouts.app :title="$website->name">
    <div class="flex flex-wrap items-start justify-between gap-6">
        <div>
            <a href="{{ route('websites.index') }}" class="text-sm text-zinc-500 hover:text-zinc-300">← Websites</a>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight">{{ $website->name }}</h1>
            <p class="mt-1 text-zinc-500">{{ $website->domain }} · {{ $website->timezone }} · {{ $website->should_track_bots ? 'bots included' : 'bots rejected' }}</p>
        </div>
        <a href="{{ route('websites.edit', $website) }}" class="rounded-lg border border-zinc-700 px-4 py-2 text-sm hover:border-zinc-500">Settings</a>
    </div>

    <div class="mt-8 flex flex-wrap items-center gap-2">
        @foreach ([7, 30, 90, 365] as $range)
            <a href="{{ route('websites.show', [$website, 'days' => $range]) }}" class="rounded-full px-3 py-1 text-sm {{ $days === $range ? 'bg-white text-zinc-950' : 'bg-zinc-900 text-zinc-400 hover:text-white' }}">{{ $range }} days</a>
        @endforeach
    </div>

    <div class="mt-6 rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
        <p class="text-sm text-zinc-500">Requests</p>
        <p class="mt-2 text-4xl font-semibold tabular-nums">{{ number_format($requests) }}</p>
        <p class="mt-2 text-sm text-zinc-500">Derived from the path counter. There is intentionally no unique visitor number.</p>
    </div>

    @php
        $labels = [
            'path' => 'Paths',
            'country' => 'Countries',
            'browser' => 'Browsers',
            'os' => 'Operating systems',
            'device' => 'Devices',
            'format' => 'Formats',
        ];
    @endphp

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @foreach ($labels as $metric => $label)
            <section class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
                <h2 class="font-medium">{{ $label }}</h2>
                <div class="mt-4 divide-y divide-zinc-800">
                    @forelse (($metrics->get($metric) ?? collect())->take(10) as $row)
                        <div class="flex items-center justify-between gap-4 py-2 text-sm">
                            <span class="min-w-0 truncate text-zinc-300">{{ $row->value }}</span>
                            <span class="shrink-0 tabular-nums text-zinc-500">{{ number_format($row->count) }}</span>
                        </div>
                    @empty
                        <p class="py-3 text-sm text-zinc-600">No data.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    <section class="mt-8 rounded-xl border border-zinc-800 p-6">
        <h2 class="font-medium">Browser collection</h2>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-400">The script derives browser, OS and device from the request in memory. Raw IP addresses and User-Agent strings are never written to analytics storage.</p>

        <pre class="mt-4 overflow-x-auto rounded-lg bg-black/40 p-4 text-sm text-zinc-300"><code>&lt;script defer data-website-id="{{ $website->uuid }}" src="{{ url('/script.js') }}"&gt;&lt;/script&gt;</code></pre>
    </section>

    <section class="mt-6 rounded-xl border border-zinc-800 p-6">
        <h2 class="font-medium">Server-side collection</h2>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-400">Send only the coarse values you actually want to keep. This lets the analytics server avoid receiving the visitor's raw IP or User-Agent at all.</p>

        <pre class="mt-4 overflow-x-auto rounded-lg bg-black/40 p-4 text-sm text-zinc-300"><code>POST {{ route('collect.processed', $website) }}

{
  "path": "/blog/example",
  "country": "DE",
  "browser": "Firefox",
  "os": "Linux",
  "device": "desktop",
  "format": "markdown"
}</code></pre>
    </section>
</x-layouts.app>
