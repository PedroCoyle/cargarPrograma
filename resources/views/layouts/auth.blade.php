<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Programas Académicos')</title>

    @vite(['resources/css/app.css'])
</head>

<body class="auth-page">

    <main class="auth-wrapper">

        <div class="auth-card">

            <div class="auth-header">

                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Logo institucional"
                    class="auth-logo"
                >

                <h1>EEST N°2</h1>

                <p>Programas Académicos</p>

            </div>

            <div class="auth-body">

                @if ($errors->any())
                    <div class="auth-errors">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @if (session('success'))
                    <div class="auth-success">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')

            </div>

        </div>

    </main>

</body>
</html>