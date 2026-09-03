<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel')</title>

    <style>
        body {
            margin: 0;
            background-color: #ffffff;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background-color: #f4f4f4;
            border-right: 1px solid #ddd;
        }

        .app-content {
            flex: 1;
            background-color: #ffffff;
        }
    </style>
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