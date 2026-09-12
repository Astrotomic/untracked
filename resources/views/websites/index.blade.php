<x-layouts.app title="Websites">
    <div class="flex items-end justify-between gap-6">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Websites</h1>
            <p class="mt-2 text-zinc-400">Count requests. Never create visitors.</p>
        </div>
        <a href="{{ route('websites.create') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-zinc-950 hover:bg-zinc-200">Add website</a>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2">
        @forelse ($websites as $website)
            @php
                $stats = $websiteStats->get($website->getKey());
                $trendClass = match ($stats['trend_direction']) {
                    'up' => 'text-emerald-400',
                    'down' => 'text-red-400',
                    default => 'text-zinc-500',
                };
                $trendIcon = match ($stats['trend_direction']) {
                    'up' => 'trending-up',
                    'down' => 'trending-down',
                    default => 'minus',
                };
            @endphp

            <a href="{{ route('websites.show', $website) }}" class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5 transition hover:border-zinc-700">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-medium">{{ $website->name }}</h2>
                        <p class="mt-1 text-sm text-zinc-500">{{ $website->domain }}</p>
                    </div>
                    <span class="rounded-full border border-zinc-700 px-2 py-1 text-xs text-zinc-400">{{ $website->should_track_bots ? 'bots on' : 'bots off' }}</span>
                </div>

                <div class="mt-6 grid grid-cols-[minmax(0,1fr)_auto_auto] items-end gap-5">
                    <div class="h-12 min-w-0">
                        <canvas data-website-sparkline="{{ $website->getKey() }}"></canvas>
                    </div>

                    <div class="text-right">
                        <p class="text-xl font-semibold tabular-nums text-zinc-100">{{ number_format($stats['today']) }}</p>
                        <p class="mt-0.5 text-xs text-zinc-500">human today</p>
                    </div>

                    <div class="text-right" title="Last 7 complete days compared with the previous 7 complete days">
                        <p class="flex items-center justify-end gap-1 text-sm font-medium tabular-nums {{ $trendClass }}">
                            <x-icon.lucide :name="$trendIcon" class="size-3.5" />
                            @if ($stats['trend_direction'] === 'flat')
                                steady
                            @elseif ($stats['trend_percentage'] === null)
                                new
                            @else
                                {{ $stats['trend_percentage'] }}%
                            @endif
                        </p>
                        <p class="mt-0.5 text-xs text-zinc-500">7d trend</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-800 p-10 text-center text-zinc-500 md:col-span-2">
                No websites yet.
            </div>
        @endforelse
    </div>

    @if ($websites->isNotEmpty())
        <script type="application/json" id="website-list-data">@json($websiteStats)</script>
    @endif
</x-layouts.app>
