<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'جمه') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Bootstrap User Data -->
    <script>
        window.__USER__ = @json(auth()->user());
    </script>

    <!-- Scripts -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/react-app.jsx'])
</head>
<body class="font-sans antialiased bg-gray-900 text-white">
    <div id="root"></div>
</body>
</html>
