<div class="{{ $class }}">
    <ul class="breadcrumbs__list">
        <li class="breadcrumbs__item">
            <a href="/" class="breadcrumbs__link">Главная</a>
        </li>

        @foreach ($breadcrumbs as $index => $crumb)
            <li class="breadcrumbs__item">
                @if ($loop->last)
                    <!-- Последний элемент — без ссылки -->
                    <span class="breadcrumbs__text">{{ $crumb['title'] }}</span>
                @else
                    <!-- Остальные — со ссылками -->
                    <a href="{{ $crumb['url'] }}" class="breadcrumbs__link">{{ $crumb['title'] }}</a>
                @endif
            </li>
        @endforeach
    </ul>
</div>
