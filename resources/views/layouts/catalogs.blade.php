<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <app-layout
            :user-name="'{{ auth()->user()->name ?? 'Usuario' }}'"
            :user-email="'{{ auth()->user()->email ?? 'usuario@ejemplo.com' }}'"
            :profile-pic="'{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=User&background=random' }}'"
            :theme="'{{ session('theme', 'light') }}'"
            dashboard-link="/"
            orders-link="/orders"
            customers-link="/customers"
            packages-link="/paquetes"
            signatures-link="/signatures"
            settings-link="/settings"
            :show-settings="true"
        >
            <catalogs-board></catalogs-board>
        </app-layout>
    </div>
</body>
</html>