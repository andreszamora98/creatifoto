<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.head-theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"
         data-user-name="{{ auth()->user()->name ?? 'Usuario' }}"
         data-user-email="{{ auth()->user()->email ?? 'usuario@ejemplo.com' }}"
         data-profile-pic="{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=User&background=random' }}"></div>
</body>
</html>