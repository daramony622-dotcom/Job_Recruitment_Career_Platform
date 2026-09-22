<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Continue with Telegram - Job Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white flex items-center justify-center p-6">
    @php
        $mode = request('mode') === 'register' ? 'register' : 'login';
        $botName = ltrim(config('services.telegram.username', ''), '@');
        $callbackUrl = route('telegram.callback', [
            'mode' => $mode,
            'phone' => request('phone'),
        ]);
    @endphp

    <main class="w-full max-w-md rounded-3xl border border-slate-800 bg-slate-900 p-8 text-center shadow-2xl">
        <img src="{{ config('app.frontend_url') }}/logo.png" alt="Job Search" class="mx-auto h-16 w-auto object-contain" />
        <div class="mx-auto mt-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-500/15 text-sky-400">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M21.4 4.6 18.2 19.7c-.24 1.07-.87 1.33-1.77.83l-4.86-3.58-2.35 2.26c-.26.26-.48.48-.98.48l.35-4.95 9.02-8.15c.39-.35-.09-.55-.61-.2L5.85 13.5 1.12 12.02c-1.03-.32-1.05-1.03.22-1.51L19.8 3.34c.86-.32 1.61.2 1.6 1.26Z" />
            </svg>
        </div>
        <p class="mt-6 text-xs font-bold uppercase tracking-[0.2em] text-sky-400">Telegram verification</p>
        <h1 class="mt-3 text-2xl font-black">{{ $mode === 'register' ? 'Finish your registration' : 'Sign in securely' }}</h1>
        <p class="mt-3 text-sm leading-6 text-slate-400">
            {{ $mode === 'register' ? 'Confirm your Telegram account to continue with your name and phone number.' : 'Confirm your Telegram account to continue to Job Search.' }}
        </p>

        @if ($botName)
            <div class="mt-8 flex justify-center">
                <script
                    async
                    src="https://telegram.org/js/telegram-widget.js?22"
                    data-telegram-login="{{ $botName }}"
                    data-size="large"
                    data-auth-url="{{ $callbackUrl }}"
                    data-request-access="write"
                    data-userpic="true"
                ></script>
            </div>
        @else
            <p class="mt-8 rounded-xl border border-rose-900/60 bg-rose-950/40 p-3 text-sm text-rose-300">Telegram is not configured yet.</p>
        @endif

        <a href="{{ config('app.frontend_url') }}/{{ $mode === 'register' ? 'register' : 'login' }}" class="mt-8 inline-block text-sm font-semibold text-slate-400 hover:text-white">Back to Job Search</a>
    </main>
</body>
</html>
