<?php

namespace App\Orchid\Screens;

use App\Models\Page;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PageListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        return [
            'pages' => Page::filters()
                ->defaultSort('id', 'asc')
                ->paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Страницы';
    }

    public function breadcrumbs(): array
    {
        return [
            ['title' => 'Панель управления', 'url' => route('platform.main')],
            ['title' => 'Меню сайта']
        ];
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Создать новую')
                ->icon('plus')
                ->route('platform.page.create'),
        ];
    }

    /**
     * The screen's layout elements.
     */
    public function layout(): iterable
    {
        return [
            Layout::table('pages', [
                TD::make('id', 'id')->sort(),
                TD::make('title', 'Заголовок')
                    ->render(
                        fn($page) =>
                        Link::make($page->title)
                            ->route('platform.page.edit', $page->id)
                            ->class('text-dark td-title text-decoration-none')
                    ),
                TD::make('slug', 'URL'),
                TD::make('',)
                    ->render(
                        fn($page) =>
                        Link::make()
                            ->icon('eye-fill')
                            ->route('page.show', $page->slug)
                            ->target('_blank')
                            ->class('text-dark td-title text-decoration-none')
                    ),
                TD::make('')
                    ->render(
                        fn(Page $page) =>
                        ModalToggle::make('')
                            ->icon('trash')
                            ->modal('removePage')
                            ->modalTitle("Удалить \"{$page->title}\"?")
                            ->method('remove', ['id' => $page->id])
                            ->confirm('Удалить навсегда?')
                            ->class('btn-td')
                    )
                    ->align(TD::ALIGN_RIGHT)->class('btn-td-wrap'),
            ]),

            Layout::modal('removePage', Layout::rows([]))->title('Подтвердите удаление'),
        ];
    }

    /**
     * Удаление шаблона
     */
    public function remove(int $id)
    {
        Page::findOrFail($id)->delete();
        Toast::info('Страница удалена!');
    }
}
