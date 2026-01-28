<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ffffff">
        <meta name="apple-mobile-web-app-capable" content="yes">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- Apply theme early to avoid flash of incorrect theme on navigation/reload --}}
        <script>
            (function(){
                try {
                    var serverPref = null;
                    var isAuthenticated = false;
                    @if(auth()->check())
                        isAuthenticated = true;
                        serverPref = '{{ auth()->user()->dark_mode ? 'dark' : 'light' }}';
                    @endif

                    // For unauthenticated users, FORCE light mode - remove dark class and ignore all preferences
                    if (!isAuthenticated) {
                        document.documentElement.classList.remove('dark');
                        // Override browser dark mode preference with inline style
                        document.documentElement.style.colorScheme = 'light';
                        return;
                    }

                    // For authenticated users, apply server preference first
                    if (serverPref === 'dark') {
                        document.documentElement.classList.add('dark');
                        document.documentElement.style.colorScheme = 'dark';
                        return;
                    }

                    // Fall back to localStorage if no server preference
                    var t = null;
                    try { t = localStorage.getItem('theme'); } catch (e) {}

                    if (t === 'dark') {
                        document.documentElement.classList.add('dark');
                        document.documentElement.style.colorScheme = 'dark';
                    } else if (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        document.documentElement.classList.add('dark');
                        document.documentElement.style.colorScheme = 'dark';
                    }
                } catch (e) { /* ignore */ }
            })();
        </script>
        @routes

        @if(env('USE_CDN'))
            {{-- Load CDN CSS only for prototyping. Do NOT rely on Tailwind Play in production. --}}
            {{-- If you must use the Tailwind Play CDN, guard the config to avoid errors when the script fails to load. --}}
            <script>
                if (window.tailwind) {
                    tailwind.config = { darkMode: 'class' };
                }
            </script>

            {{-- Optional external CSS hosted on your CDN. Configure in .env as CDN_CSS_URL --}}
            @if(env('CDN_CSS_URL'))
                <link rel="stylesheet" href="{{ env('CDN_CSS_URL') }}">
            @endif
        @endif

        {{-- Always include the Vite-built JS so the Inertia/Vue app mounts correctly. --}}
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])

        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        <script>
            (function(){
                function updateClientNames() {
                    try {
                        document.querySelectorAll('.client-name').forEach(function(el){
                            if (!el.dataset.full) el.dataset.full = el.textContent.trim();
                            var full = el.dataset.full || '';
                            var first = (full.split(/\s+/)[0]) || full;
                            if (window.innerWidth <= 480) {
                                el.textContent = first;
                            } else {
                                el.textContent = full;
                            }
                        });
                    } catch(e) { /* ignore */ }
                }

                window.addEventListener('resize', updateClientNames);
                document.addEventListener('DOMContentLoaded', updateClientNames);

                // Re-run after Inertia navigation events so names update on page change
                document.addEventListener('inertia:finish', updateClientNames);
                document.addEventListener('inertia:load', updateClientNames);

                // Run once shortly after load to catch any late updates
                setTimeout(updateClientNames, 50);
            })();
        </script>
    </body>
</html>
