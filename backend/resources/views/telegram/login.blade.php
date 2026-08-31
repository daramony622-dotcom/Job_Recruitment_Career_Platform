<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>JobMatrix — {{ request('tab') === 'register' ? 'Register' : 'Login' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

    @php $tab = request('tab', 'login'); @endphp

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm w-full max-w-md p-8">

        {{-- ── Logo ─────────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-center gap-2.5 mb-1">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="7" width="18" height="14" rx="2" />
                    <path d="M8 7V5a4 4 0 0 1 8 0v2" stroke-linecap="round"/>
                    <circle cx="12" cy="14" r="2" fill="currentColor" stroke="none"/>
                </svg>
            </div>
            <span class="text-xl font-semibold text-gray-900">Job<span class="text-blue-600">Matrix</span></span>
        </div>

        <p class="text-center text-sm text-gray-500 mt-1 mb-6">
            {{ $tab === 'register' ? 'Create your account to get started.' : 'Welcome back! Please enter your details.' }}
        </p>

        {{-- ── Tab switcher ─────────────────────────────────────────────────── --}}
        <div class="flex bg-gray-100 rounded-lg p-1 mb-6 gap-1">
            <a href="{{ route('login') }}"
               class="flex-1 py-2 text-center text-sm font-medium rounded-md transition
                      {{ $tab === 'login'
                          ? 'bg-white border border-gray-200 text-gray-900 shadow-sm'
                          : 'text-gray-500 hover:text-gray-700' }}">
                Login
            </a>
            <a href="{{ route('login', ['tab' => 'register']) }}"
               class="flex-1 py-2 text-center text-sm font-medium rounded-md transition
                      {{ $tab === 'register'
                          ? 'bg-white border border-gray-200 text-gray-900 shadow-sm'
                          : 'text-gray-500 hover:text-gray-700' }}">
                Register
            </a>
        </div>

        {{-- ── Validation errors ────────────────────────────────────────────── --}}
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- ── Flash messages ──────────────────────────────────────────────── --}}
        @if (session('info'))
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-700">{{ session('info') }}</p>
            </div>
        @endif

        {{-- ════════════════════════════════════════════════════════════════════
                LOGIN PANEL
        ════════════════════════════════════════════════════════════════════ --}}
        @if ($tab === 'login')

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Gmail or Username
                    </label>
                    <input
                        type="text"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="coca@gmail.com"
                        autocomplete="email"
                        class="w-full h-11 px-3.5 border border-gray-300 rounded-lg text-sm
                            bg-gray-50 focus:bg-white focus:outline-none focus:ring-2
                            focus:ring-blue-100 focus:border-blue-500 transition"
                        required
                    />
                </div>

                {{-- Password --}}
                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="pw-login"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="w-full h-11 px-3.5 pr-11 border border-gray-300 rounded-lg text-sm
                                bg-gray-50 focus:bg-white focus:outline-none focus:ring-2
                                focus:ring-blue-100 focus:border-blue-500 transition"
                            required
                        />
                        <button type="button" onclick="togglePassword('pw-login', 'eye-login')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eye-login" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
                                        a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
                                        M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29
                                        m7.532 7.532l3.29 3.29M3 3l3.59 3.59
                                        m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7
                                        a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Forgot password --}}
                <div class="text-right mb-5">
                    <a href="{{ route('password.request') }}"
                    class="text-sm text-blue-600 hover:underline">
                        Forgot password?
                    </a>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full h-11 bg-blue-600 hover:bg-blue-700 active:bg-blue-800
                            text-white rounded-lg font-medium text-sm transition">
                    Login
                </button>
            </form>

        {{-- ════════════════════════════════════════════════════════════════════
                REGISTER PANEL
        ════════════════════════════════════════════════════════════════════ --}}
        @else

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                {{-- First + Last name --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">First name</label>
                        <input
                            type="text"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            placeholder="John"
                            class="w-full h-11 px-3.5 border border-gray-300 rounded-lg text-sm
                                bg-gray-50 focus:bg-white focus:outline-none focus:ring-2
                                focus:ring-blue-100 focus:border-blue-500 transition"
                            required
                        />
                        @error('first_name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Last name</label>
                        <input
                            type="text"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            placeholder="Doe"
                            class="w-full h-11 px-3.5 border border-gray-300 rounded-lg text-sm
                                bg-gray-50 focus:bg-white focus:outline-none focus:ring-2
                                focus:ring-blue-100 focus:border-blue-500 transition"
                            required
                        />
                        @error('last_name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="john@example.com"
                        autocomplete="email"
                        class="w-full h-11 px-3.5 border border-gray-300 rounded-lg text-sm
                            bg-gray-50 focus:bg-white focus:outline-none focus:ring-2
                            focus:ring-blue-100 focus:border-blue-500 transition"
                        required
                    />
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="pw-reg"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            class="w-full h-11 px-3.5 pr-11 border border-gray-300 rounded-lg text-sm
                                bg-gray-50 focus:bg-white focus:outline-none focus:ring-2
                                focus:ring-blue-100 focus:border-blue-500 transition"
                            required
                        />
                        <button type="button" onclick="togglePassword('pw-reg', 'eye-reg')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eye-reg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                        -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="pw-reg2"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            class="w-full h-11 px-3.5 pr-11 border border-gray-300 rounded-lg text-sm
                                bg-gray-50 focus:bg-white focus:outline-none focus:ring-2
                                focus:ring-blue-100 focus:border-blue-500 transition"
                            required
                        />
                        <button type="button" onclick="togglePassword('pw-reg2', 'eye-reg2')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eye-reg2" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                         -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full h-11 bg-blue-600 hover:bg-blue-700 active:bg-blue-800
                               text-white rounded-lg font-medium text-sm transition">
                    Create account
                </button>
            </form>

        @endif

        {{-- ── Social auth divider ──────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 my-5">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-xs font-medium text-gray-400 tracking-widest uppercase">
                Or {{ $tab === 'register' ? 'register' : 'login' }} with
            </span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        {{-- ── Google ───────────────────────────────────────────────────────── --}}
        <a href="{{ route('auth.google') }}"
           class="flex items-center justify-center gap-2.5 w-full h-11 border border-gray-300
                  rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50
                  active:bg-gray-100 transition mb-3">
            {{-- Google "G" SVG --}}
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            {{ $tab === 'register' ? 'Register' : 'Login' }} with Google Gmail
        </a>

        {{-- ── Telegram Button ──────────────────────────────────────────────── --}}
        <a href="{{ route('auth.telegram') }}"
           class="flex items-center justify-center gap-2.5 w-full h-11 border border-gray-300
                  rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50
                  active:bg-gray-100 transition mb-3">
            {{-- Telegram Airplane SVG --}}
            <svg class="w-5 h-5 text-sky-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.69-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.38-.49 1.07-.75 4.19-1.83 6.99-3.04 8.4-3.64 4-.17 4.83.67 4.67 1.47z"/>
            </svg>
            {{ $tab === 'register' ? 'Register' : 'Login' }} with Telegram
        </a>

        {{-- ── Official Telegram Widget ─────────────────────────────────────── --}}
        @if (config('services.telegram.bot_name'))
        <div class="flex justify-center mb-3">
            <script
                async
                src="https://telegram.org/js/telegram-widget.js?22"
                data-telegram-login="{{ config('services.telegram.bot_name') }}"
                data-size="large"
                data-auth-url="{{ route('auth.telegram.callback') }}"
                data-request-access="write"
                data-userpic="true"
            ></script>
        </div>
        @endif

        {{-- ── Footer link ─────────────────────────────────────────────────── --}}
        <p class="text-center text-sm text-gray-500 mt-5">
            @if ($tab === 'login')
                No account yet?
                <a href="{{ route('login', ['tab' => 'register']) }}"
                   class="text-blue-600 font-semibold hover:underline">Register</a>
            @else
                Already have an account?
                <a href="{{ route('login') }}"
                   class="text-blue-600 font-semibold hover:underline">Login</a>
            @endif
        </p>

    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId)
            const isPassword = input.type === 'password'
            input.type = isPassword ? 'text' : 'password'

            const icon = document.getElementById(iconId)
            if (isPassword) {
                // Show "eye-off" (hidden) icon
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
                             a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
                             M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29
                             m7.532 7.532l3.29 3.29M3 3l3.59 3.59
                             m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7
                             a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `
            } else {
                // Show "eye" (visible) icon
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                             -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `
            }
        }
    </script>

</body>
</html>