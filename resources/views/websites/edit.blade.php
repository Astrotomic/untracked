<x-layouts.app :title="'Edit '.$website->name">
    <div class="max-w-2xl">
        <a
            href="{{ route('websites.show', $website) }}"
            class="text-sm text-zinc-500 hover:text-zinc-300"
            >← {{ $website->name }}</a
        >
        <h1 class="mt-4 text-3xl font-semibold tracking-tight">Edit website</h1>

        <form
            method="POST"
            action="{{ route('websites.update', $website) }}"
            class="mt-8"
        >
            @method ('PUT')
            @include ('websites._form', ['website' => $website])
        </form>

        <form
            method="POST"
            action="{{ route('websites.destroy', $website) }}"
            class="mt-10 border-t border-zinc-800 pt-6"
            onsubmit="return confirm('Delete this website and all of its metrics?');"
        >
            @csrf
            @method ('DELETE')
            <button class="text-sm text-red-400 hover:text-red-300">Delete website</button>
        </form>
    </div>
</x-layouts.app>
