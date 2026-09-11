<x-layouts.app title="Login">
    <div class="mx-auto max-w-md">
        <h1 class="text-3xl font-semibold tracking-tight">Login</h1>
        <p class="mt-2 text-zinc-400">Analytics without visitors.</p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
            @csrf

            <label class="block">
                <span class="text-sm text-zinc-300">Email</span>
                <input name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 outline-none focus:border-zinc-500">
            </label>

            <label class="block">
                <span class="text-sm text-zinc-300">Password</span>
                <input name="password" type="password" required class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 outline-none focus:border-zinc-500">
            </label>

            <label class="flex items-center gap-2 text-sm text-zinc-400">
                <input type="checkbox" name="remember" value="1" class="rounded border-zinc-700 bg-zinc-900">
                Remember me
            </label>

            @if ($errors->any())
                <p class="text-sm text-red-400">{{ $errors->first() }}</p>
            @endif

            <button class="w-full rounded-lg bg-white px-4 py-2 font-medium text-zinc-950 hover:bg-zinc-200">Login</button>
        </form>
    </div>
</x-layouts.app>
