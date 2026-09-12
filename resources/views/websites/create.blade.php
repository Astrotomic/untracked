<x-layouts.app title="Add website">
    <div class="max-w-2xl">
        <a
            href="{{ route('websites.index') }}"
            class="text-sm text-zinc-500 hover:text-zinc-300"
            >← Websites</a
        >
        <h1 class="mt-4 text-3xl font-semibold tracking-tight">Add website</h1>

        <form
            method="POST"
            action="{{ route('websites.store') }}"
            class="mt-8"
        >
            @include ('websites._form', ['website' => null])
        </form>
    </div>
</x-layouts.app>
