@props (['title' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    />
    <title>{{ $title ? $title.' · ' : '' }}Untracked</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
    <header class="border-b border-zinc-800">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a
                href="{{ auth()->check() ? route('websites.index') : route('login') }}"
                class="font-semibold tracking-tight"
                >Untracked</a
            >

            @auth
                <div class="flex items-center gap-4 text-sm text-zinc-400">
                    <span>{{ auth()->user()->email }}</span>
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf
                        <button class="hover:text-white">Log out</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-10">{{ $slot }}</main>
</body>
</html>
