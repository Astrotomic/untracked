@csrf

<div class="space-y-5">
    <label class="block">
        <span class="text-sm text-zinc-300">Name</span>
        <input
            name="name"
            value="{{ old('name', $website?->name) }}"
            required
            class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 outline-none focus:border-zinc-500"
        />
        @error ('name')
            <span class="mt-1 block text-sm text-red-400">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="text-sm text-zinc-300">Domain</span>
        <input
            name="domain"
            value="{{ old('domain', $website?->domain) }}"
            placeholder="example.com"
            required
            class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 outline-none focus:border-zinc-500"
        />
        @error ('domain')
            <span class="mt-1 block text-sm text-red-400">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="text-sm text-zinc-300">Timezone</span>
        <input
            name="timezone"
            value="{{ old('timezone', $website?->timezone ?? 'UTC') }}"
            placeholder="Europe/Berlin"
            required
            class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 outline-none focus:border-zinc-500"
        />
        @error ('timezone')
            <span class="mt-1 block text-sm text-red-400">{{ $message }}</span>
        @enderror
    </label>

    <label class="flex items-start gap-3 rounded-lg border border-zinc-800 p-4">
        <input
            type="hidden"
            name="should_track_bots"
            value="0"
        />
        <input
            type="checkbox"
            name="should_track_bots"
            value="1"
            @checked (old('should_track_bots', $website?->should_track_bots ?? true))
            class="mt-1 rounded border-zinc-700 bg-zinc-900"
        />
        <span>
            <span class="block text-sm font-medium">Track bots</span>
            <span class="mt-1 block text-sm text-zinc-500">Bots become coarse <code>Bot</code>/<code>bot</code> metric values. They are never identified individually.</span>
        </span>
    </label>

    <button class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-zinc-950 hover:bg-zinc-200">Save</button>
</div>
