<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Page;
use App\Services\MenuService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PlatformScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
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
        return 'Админ-панель';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Welcome to your Orchid application.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('platform::partials.update-assets'),
            Layout::view('platform::partials.welcome'),
            Layout::rows([
                Group::make([
                    Button::make('Пересобрать меню и кэш')
                        ->method('rebuildMenu')
                        ->icon('list-stars'),
                ]),
            ]),
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
                            ->modalTitle("Удалить шаблон \"{$page->title}\"?")
                            ->method('remove', ['id' => $page->id])
                            ->confirm('Удалить навсегда?')
                            ->class('btn-td')
                    )
                    ->align(TD::ALIGN_RIGHT)->class('btn-td-wrap'),
            ]),
        ];
    }

    public function rebuildMenu()
    {
        Cache::flush(); 
        app(MenuService::class)->rebuildAllPagePaths();

        Toast::success('Кэш очищен, пути страниц обновлены');
    }
}
