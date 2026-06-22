<section class="not-found">
    <div class="not-found__container container">

        @include('components.p-header.p-header', [
            'class' => 'document__p-header p-header',
            'showTitle' => true,
            'titlePage' => $page->title,
        ])
        <a href="/" class="not-found__link">
            < Вернуться на главную</a>

    </div>
</section>
