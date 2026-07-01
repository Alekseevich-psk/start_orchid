# Blade Template Engine — Шпаргалка

## Основы

### Вывод переменных
```blade
{{ $name }}           {{-- Базовый вывод (экранирование HTML) --}}
{!! $name !!}          {{-- Вывод без экранирования (dangerous!) --}}
{{ $name or 'Default' }} {{-- Оператор "или" (значение по умолчанию) --}}
```

### Комментарии
```blade
{{-- Это комментарий Blade — не попадёт в HTML --}}
<!-- Обычный HTML-комментарий -->
```

### Условия
```blade
@if (count($records) === 1)
    Имеется одна запись.
@elseif (count($records) > 1)
    Имеется несколько записей.
@else
    Записей нет.
@endif

@unless ($user->isVerified)
    Пользователь не подтверждён.
@endunless

@isset($name)
    Переменная $name установлена и не null.
@endisset

@empty($items)
    Массив $items пуст или не существует.
@endempty
```

### Циклы
```blade
@for ($i = 0; $i < 10; $i++)
    Итерация: {{ $i }}
@endfor

@foreach ($users as $user)
    Имя: {{ $user->name }}
@endforeach

@while ($condition)
    Условие истинно.
@ endwhile
```

### Операторы циклов
```blade
@foreach ($users as $user)
    {{ $loop->index }}      {{-- Индекс текущей итерации (начинается с 0) --}}
    {{ $loop->iteration }}  {{-- Номер итерации (начинается с 1) --}}
    {{ $loop->remaining }}  {{-- Количество оставшихся итераций --}}
    {{ $loop->count }}      {{-- Общее количество элементов --}}
    {{ $loop->first }}      {{-- true, если это первая итерация --}}
    {{ $loop->last }}       {{-- true, если это последняя итерация --}}
    {{ $loop->even }}       {{-- true, если номер итерации чётный --}}
    {{ $loop->odd }}        {{-- true, если номер итерации нечётный --}}
    {{ $loop->depth }}      {{-- Вложенность цикла (для вложенных циклов) --}}
    {{ $loop->parent }}     {{-- Доступ к родительскому циклу (в вложенных циклах) --}}
@endforeach
```

### Директивы компонентов

#### Простой компонент
```blade
@component('components.alert')
    @slot('title')
        Преду��реждение
    @endslot
    
    Содержимое компонента
@endcomponent

{{-- Сокращённая запись (自 Laravel 5.4+) --}}
@component('components.alert', ['title' => 'Предупреждение'])
    Содержимое компонента
@endcomponent
```

#### Строгий компонент
```blade
{{-- В шаблоне --}}
<x-alert type="error" title="Ошибка">
    {{ $message }}
</x-alert>

{{-- Структура компонента: resources/views/components/alert.blade.php --}}
<div class="alert alert-{{ $type }}">
    <strong>{{ $title }}</strong>
    <slot></slot>
</div>
```

#### Class & Style компоненты
```blade
<x-button class="btn-primary" :active="$isActive">
    Нажми меня
</x-button>

{{-- Динамические классы --}}
<x-button :class="['btn' => true, 'btn-primary' => $isPrimary]">
    Кнопка
</x-button>
```

### Директивы вставки (slots)
```blade
{{-- Шаблон с несколькими слотами --}}
@component('layouts.app')
    @slot('header')
        <h1>Заголовок</h1>
    @endslot
    
    Основное содержимое (по умолчанию в $slot)
    
    @slot('footer')
        <footer>Подвал</footer>
    @endslot
@endcomponent
```

### Директивы включения
```blade
{{-- Включение другого Blade-шаблона --}}
@include('partials.header', ['title' => 'Главная'])

{{-- Включение только если шаблон существует --}}
@includeIf('partials.sidebar', ['items' => $menu])

{{-- Включение шаблона из переменной --}}
@includeWhen($condition, 'partials.content')
```

### Директивы стеков (Stacks)
```blade
{{-- В главном шаблоне --}}
<head>
    <title>Мой сайт</title>
    @stack('styles')
    @stack('scripts')
</head>

{{-- В дочернем шаблоне --}}
@push('styles')
    <link rel="stylesheet" href="/css/custom.css">
@endpush

@push('scripts')
    <script src="/js/app.js"></script>
@endpush

{{-- Вставка стека с содержимым по умолчанию --}}
@stack('styles', [
    '<link rel="stylesheet" href="/css/main.css">',
    '<link rel="stylesheet" href="/css/theme.css">'
])
```

### Директивы пути и URL
```blade
{{ asset('css/app.css') }}
{{ url('/admin/dashboard') }}
{{ route('users.index') }}
{{ route('users.show', ['id' => 1]) }}
{{ action('UserController@index') }}
{{ config('app.name') }}
```

### Директивы форм
```blade
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    @method('PUT')
    
    <input type="text" name="name" value="{{ old('name', $user->name) }}">
    @error('name')
        <div class="error">{{ $message }}</div>
    @enderror
    
    <button type="submit">Сохранить</button>
</form>
```

### Директива auth
```blade
@guest
    <a href="{{ route('login') }}">Войти</a>
@endguest

@auth
    <p>Привет, {{ auth()->user()->name }}!</p>
@endauth

@auth('admin')
    <p>Привет, администратор!</p>
@endauth

@auth('web')
    <p>Привет, пользователь!</p>
@endauth
```

### Директива dump
```blade
@dump($variable)
@dd($variable)        {{-- dump + die --}}
@dd($a, $b, $c)      {{-- dump нескольких переменных + die --}}
```

### Директива extends и yield
```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Заголовок по умолчанию')</title>
    @stack('head')
</head>
<body>
    @include('partials.nav')
    
    <div class="container">
        @yield('content')
    </div>
    
    @stack('footer')
</body>
</html>

{{-- resources/views/pages/home.blade.php --}}
@extends('layouts.app')

@section('title', 'Главная страница')

@push('head')
    <link rel="stylesheet" href="/css/home.css">
@endpush

@section('content')
    <h1>Добро пожаловать!</h1>
    <p>Это главная страница.</p>
@endsection

@push('footer')
    <script src="/js/home.js"></script>
@endpush
```

### Директива section
```blade
@section('sidebar')
    <div class="widget">
        <h3>Боковая панель</h3>
        <p>Контент боковой панели</p>
    </div>
@show

{{-- continuation: @parent вставляет родительский контент --}}
@section('sidebar')
    @parent
    <p>Дополнительный контент</p>
@endsection
```

### Директивы макроса (для классов)
```blade
{{-- В сервис-провайдере (например, AppServiceProvider) --}}
use Illuminate\Support\Facades\Blade;

Blade::directive('datetime', function ($expression) {
    return "<?php echo with($expression)->format('d.m.Y H:i'); ?>";
});

Blade::directive('currency', function ($expression) {
    return "<?php echo number_format($expression, 2, '.', ' ') . ' ₽'; ?>";
});

{{-- В шаблоне --}}
<p>Дата: @datetime($createdAt)</p>
<p>Цена: @currency($price)</p>
```

### Директива inject
```blade
@inject('metrics', 'App\Services\MetricsService')

<p>Совершённых покупок: {{ $metrics->getTotalSales() }}</p>
<p>Средний чек: {{ $metrics->getAverageOrderValue() }}</p>
```

### Директива stack с pull
```blade
{{-- @push добавляет в стек --}}
@push('scripts')
    <script src="/js/custom.js"></script>
@endpush

{{-- @pull возвращает содержимое стека и очищает его --}}
<head>
    <title>Мой сайт</title>
    @stack('head.styles')
</head>
<body>
    <div class="content">
        @yield('content')
    </div>
    
    @push('footer.scripts')
        <script src="/js/main.js"></script>
    @endpush
    
    {{-- Используем @pull в другом месте --}}
    @stack('footer.scripts')
</body>
```

### Директива includeUnless, includeWhen
```blade
{{-- Включает только если условие истинно --}}
@includeWhen($user->isAdmin(), 'admin.dashboard')

{{-- Включает только если условие ложно --}}
@includeUnless($user->isGuest(), 'user.profile')

{{-- Аналог @includeIf --}}
@includeIf('partials.optional-section')
```

### Директива continue и break
```blade
@for ($i = 0; $i < 10; $i++)
    @if ($i === 5)
        @continue
    @endif
    
    {{ $i }}
    
    @if ($i === 8)
        @break
    @endif
@endfor
```

### Директива php
```blade
{{-- Выполнение PHP-кода (редко нужно) --}}
@php
    $counter = 0;
    foreach ($items as $item) {
        $counter++;
    }
@endphp

<p>Всего элементов: {{ $counter }}</p>
```

### Директива @lang и @choice
```blade
{{-- Локализация --}}
<p>@lang('messages.welcome')</p>
<p>@choice('messages.apples', 5)</p>
```

### Директива @each
```blade
{{-- Вставка под-view для каждого элемента массива --}}
@each('partials.user', $users, 'user', 'partials.empty')

{{-- Эквивалент: --}}
@foreach ($users as $user)
    @include('partials.user', ['user' => $user])
@endforeach

@empty
    @include('partials.empty')
@endempty
```

### Директива @stack с содержимым по умолчанию
```blade
{{-- В родительском шаблоне --}}
<head>
    @stack('styles')
    @stack('scripts')
</head>

{{-- В дочернем шаблоне можно передать содержимое по умолчанию --}}
@stack('styles', [
    '<link rel="stylesheet" href="/css/bootstrap.css">',
    '<link rel="stylesheet" href="/css/custom.css">'
])
```

## Примеры использования

### Шаблон формы с ошибками
```blade
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    
    <div class="form-group">
        <label for="name">Имя:</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}">
        @error('name')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}">
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
    
    <button type="submit">Сохранить</button>
</form>
```

### Пагинация
```blade
<div class="pagination">
    {{ $users->links() }}
    
    {{-- Показать только предыдущую/следующую кнопку --}}
    {{ $users->links('pagination::simple-default') }}
</div>
```

### Вывод JSON
```blade
<script>
    const user = @json($user);
    console.log(user);
</script>
```

### Умное экранирование
```blade
{{-- Экранируется автоматически --}}
<p>{{ $user->name }}</p>

{{-- Без экранирования (осторожно!) --}}
<p>{!! $user->bio !!}</p>

{{-- Экранирование HTML-сущностей вручную --}}
{{ e($html) }}

{{-- Вывод с условием --}}
{{ $user->name ?? 'Гость' }}
{{ $user->name ?: 'Гость' }}

{{-- Многострочный текст --}}
<pre>{{ $code }}</pre>
```

## Лучшие практики

1. **Используйте компоненты вместо @include** — компоненты безопаснее и быстрее
2. **Избегайте @php** — выносите логику в контроллеры или сервисы
3. **Делайте экранирование по умолчанию** — используйте `{{ }}`, а не `{!! !!}`
4. **Используйте компоненты для повторяющихся элементов** — кнопки, карточки, формы
5. **Минимизируйте использование @extends** — Prefer компоненты
6. **Используйте @dump/@dd только в разработке** — удаляйте перед продакшеном
7. **Кэшируйте Blade** — Blade кэшируется автоматически, но можно очистить: `php artisan view:clear`
8. **Используйте @push/@stack для CSS/JS** — позволяет подключать скрипты в нужном месте

## Полезные команды

```bash
# Очистка кэша Blade
php artisan view:clear

# Просмотр скомпилированного HTML
php artisan view:cache

# Проверка, скомпилирован ли шаблон
php artisan view:clear --force
```

## Разница между `{{ }}` и `{!! !!}`

| Директива | Экранирование | Использование |
|-----------|---------------|---------------|
| `{{ $var }}` | Да (HTML entities) | По умолчанию, безопасно |
| `{!! $var !!}` | Нет | Только для доверенного HTML |

## Эквиваленты PHP в Blade

| PHP | Blade |
|-----|-------|
| `<?php echo $var; ?>` | `{{ $var }}` |
| `<?php if ($cond): ?>` | `@if ($cond)` |
| `<?php else: ?>` | `@else` |
| `<?php endif; ?>` | `@endif` |
| `<?php foreach ($arr as $item): ?>` | `@foreach ($arr as $item)` |
| `<?php endforeach; ?>` | `@endforeach` |
| `<?php while ($cond): ?>` | `@while ($cond)` |
| `<?php endwhile; ?>` | `@endwhile` |
| `<?php break; ?>` | `@break` |
| `<?php continue; ?>` | `@continue` |

---

**Автор**: Blade Template Engine  
**Версия**: Laravel 10+  
**Обновлено**: 2026
