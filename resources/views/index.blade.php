<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ORCHID CMS</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="title">Laravel Orchid</div>
            <div class="quote">Ларавел Орчид - Платформа быстрой разработки</div>
            <div class="links">
                <a href="https://orchid.software/">Официальный сайт</a>
                <a href="https://github.com/orchidsoftware/platform">GitHub</a>
            </div>

            @include('navigation')
            
            @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                <div class="breadcrumbs">
                    @foreach ($breadcrumbs as $index => $crumb)
                        @if ($index === count($breadcrumbs) - 1)
                            <span class="breadcrumb-item active">{{ $crumb['title'] }}</span>
                        @else
                            <a href="{{ $crumb['url'] }}" class="breadcrumb-item">{{ $crumb['title'] }}</a>
                            <span class="breadcrumb-separator"> &gt; </span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        // Make menu tree available globally for JS
        window.siteMenuTree = @json($siteMenuTree);
    </script>
    <script src="/vendor/orchid/js/orchid-menu.js"></script>
</body>
</html>