<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.head-theme')
    <style>
        html, body { background-color: #f9fafb; }
        html.dark, html.dark body { background-color: #111827; }
        #app {
            opacity: 0;
            filter: blur(14px);
            transition: opacity .6s ease, filter .6s ease;
        }
        #app.app-ready {
            opacity: 1;
            filter: blur(0);
        }
        #app.app-done { filter: none; transition: none; }

        @media (prefers-reduced-motion: reduce) {
            #app { transition-duration: .01ms; }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"
         data-user-name="{{ auth()->user()->name ?? 'Usuario' }}"
         data-user-email="{{ auth()->user()->email ?? 'usuario@ejemplo.com' }}"
         data-profile-pic="{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=User&background=random' }}"></div>

    {{-- Respaldo: si el JS falla, no dejar la página invisible --}}
    <noscript><style>#app { opacity: 1; filter: none; }</style></noscript>
</body>
</html> 