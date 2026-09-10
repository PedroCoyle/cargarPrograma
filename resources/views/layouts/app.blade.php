<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Panel')</title>

    @vite(['resources/css/app.css'])
</head>
<body>

    <div class="app-container">

        @include('partials.sidebar')

        <main class="app-content">
            @yield('content')
        </main>

    </div>

</body>
</html>