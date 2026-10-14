<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- El enlace de recuperación lleva un token en la URL: que no viaje en el Referer a la fuente de Google. --}}
    <meta name="referrer" content="no-referrer">
    <title>Iniciar sesión · Creatifoto</title>
    @include('layouts.partials.head-theme')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/auth.js'])
</head>
<body>
    <div id="auth-app"
         data-view="{{ $view ?? 'login' }}"
         data-token="{{ $token ?? '' }}"
         data-email="{{ $email ?? '' }}"></div>
</body>
</html>
