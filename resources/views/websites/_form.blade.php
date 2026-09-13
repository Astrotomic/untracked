@csrf

@php
    $metricOptions = [
        \App\Enums\Metric::Path->value => [
            'metric' => \App\Enums\Metric::Path,
            'label' => 'Paths',
            'description' => 'Requested paths, request totals and traffic trends.',
        ],
        \App\Enums\Metric::Country->value => [
            'metric' => \App\Enums\Metric::Country,
            'label' => 'Countries',
            'description' => 'Country resolved from the request IP address.',
        ],
        \App\Enums\Metric::Client->value => [
            'metric' => \App\Enums\Metric::Client,
            'label' => 'Clients',
            'description' => 'Browser or client family derived from the User-Agent.',
        ],
        \App\Enums\Metric::OperatingSystem->value => [
            'metric' => \App\Enums\Metric::OperatingSystem,
            'label' => 'Operating systems',
            'description' => 'Operating system family derived from the User-Agent.',
        ],
        \App\Enums\Metric::Device->value => [
            'metric' => \App\Enums\Metric::Device,
            'label' => 'Devices',
            'description' => 'Coarse device type, including the bot traffic dimension.',
        ],
        \App\Enums\Metric::Format->value => [
            'metric' => \App\Enums\Metric::Format,
            'label' => 'Formats',
            'description' => 'Content format such as HTML or Markdown.',
        ],
        \App\Enums\Metric::Referrer->value => [
            'metric' => \App\Enums\Metric::Referrer,
            'label' => 'Referrers',
            'description' => 'Normalized referring domain.',
        ],
        \App\Enums\Metric::UtmSource->value => [
            'metric' => \App\Enums\Metric::UtmSource,
            'label' => 'UTM source',
            'description' => 'The utm_source campaign parameter.',
        ],
        \App\Enums\Metric::UtmMedium->value => [
            'metric' => \App\Enums\Metric::UtmMedium,
            'label' => 'UTM medium',
            'description' => 'The utm_medium campaign parameter.',
        ],
        \App\Enums\Metric::UtmCampaign->value => [
            'metric' => \App\Enums\Metric::UtmCampaign,
            'label' => 'UTM campaign',
            'description' => 'The utm_campaign campaign parameter.',
        ],
        \App\Enums\Metric::UtmTerm->value => [
            'metric' => \App\Enums\Metric::UtmTerm,
            'label' => 'UTM term',
            'description' => 'The utm_term campaign parameter.',
        ],
        \App\Enums\Metric::UtmContent->value => [
            'metric' => \App\Enums\Metric::UtmContent,
            'label' => 'UTM content',
            'description' => 'The utm_content campaign parameter.',
        ],
    ];
@endphp

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

    <section class="rounded-lg border border-zinc-800 p-4">
        <h2 class="font-medium">Metrics</h2>
        <p class="mt-1 text-sm leading-6 text-zinc-500">Only enabled metrics are persisted and shown on the dashboard. Disabling a metric affects future collection; existing counters are kept.</p>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ($metricOptions as $key => $option)
                <label class="flex items-start gap-3 rounded-lg bg-zinc-900/60 p-3">
                    <input
                        type="hidden"
                        name="metric_preferences[{{ $key }}]"
                        value="0"
                    />
                    <input
                        type="checkbox"
                        name="metric_preferences[{{ $key }}]"
                        value="1"
                        @checked (old("metric_preferences.{$key}", $website?->tracks($option['metric']) ?? true))
                        class="mt-1 rounded border-zinc-700 bg-zinc-900"
                    />
                    <span>
                        <span class="block text-sm font-medium">{{ $option['label'] }}</span>
                        <span class="mt-1 block text-xs leading-5 text-zinc-500">{{ $option['description'] }}</span>
                        @error ("metric_preferences.{$key}")
                            <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                        @enderror
                    </span>
                </label>
            @endforeach
        </div>
    </section>

    <button class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-zinc-950 hover:bg-zinc-200">Save</button>
</div>
