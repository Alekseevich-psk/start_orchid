<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Start orchid')</title>
    <base href="{{ config('app.url') }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="@yield('title', 'Start orchid')">
    <meta property="og:title" content="@yield('title', 'Start orchid')">
    <meta property="og:description" content="@yield('description', 'Start orchid')">
    <meta property="og:image" content="@yield('imagePage', '/images/def-pic-01.jpg')">
    <meta property="og:image:width" content="500">
    <meta property="og:image:height" content="300">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', 'Start orchid')">
    <meta name="twitter:description" content="@yield('description', 'Start orchid')">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/styles/styles.scss', 'resources/scripts/scripts.ts'])

    <meta name="description" content="@yield('description', 'Start orchid!')">
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900">

    @include('components.header.header')

    <main>
        @yield('content')
    </main>

    @include('components.footer.footer')
    @include('components.popup.popup')

</body>

</html>
