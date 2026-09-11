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
            <a href="{{ route('websites.show', $website) }}" class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5 hover:border-zinc-700">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-medium">{{ $website->name }}</h2>
                        <p class="mt-1 text-sm text-zinc-500">{{ $website->domain }}</p>
                    </div>
                    <span class="rounded-full border border-zinc-700 px-2 py-1 text-xs text-zinc-400">{{ $website->track_bots ? 'bots on' : 'bots off' }}</span>
                </div>
            </a>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-800 p-10 text-center text-zinc-500 md:col-span-2">
                No websites yet.
            </div>
        @endforelse
    </div>
</x-layouts.app>
