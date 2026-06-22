<?php

namespace App\Orchid\Screens;

use App\Models\Template;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Html;

class TemplateListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        return [
            'templates' => Template::filters()
                ->defaultSort('id', 'ASC')
                ->paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Шаблоны';
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Создать шаблон')
                ->icon('plus')
                ->route('platform.template.create'),
        ];
    }

    /**
     * The screen's layout elements.
     */
    public function layout(): iterable
    {
        return [
            Layout::table('templates', [
                TD::make('id', 'id')->sort(),
                TD::make('title', 'Заголовок')
                    ->render(
                        fn(Template $template) =>
                        Link::make($template->title)
                            ->route('platform.template.edit', $template->id)
                            ->class('text-dark td-title text-decoration-none')
                    ),
                TD::make('path', 'Путь'),
                TD::make('created_at', 'Создан')->sort(),

                TD::make('actions', 'Действия')
                    ->align(TD::ALIGN_CENTER)
                    ->width('180px')
                    ->render(function (Template $template) {
                        return
                            Link::make("Редактировать")
                            ->route('platform.template.edit', $template->id)
                            ->icon('pencil')

                            . " "

                            . ModalToggle::make('Удалить')
                            ->modal('removeTemplate')
                            ->modalTitle("Удалить шаблон «{$template->title}»?")
                            ->method('remove')
                            ->asyncParameters(['id' => $template->id])
                            ->icon('trash')
                            ->confirm('Удалить навсегда?');
                    }),

                // TD::make('')
                //     ->render(
                //         fn(Template $template) =>
                //         ModalToggle::make('')
                //             ->icon('trash')
                //             ->modal('removeTemplate')
                //             ->modalTitle("Удалить шаблон \"{$template->title}\"?")
                //             ->method('remove', ['id' => $template->id])
                //             ->confirm('Удалить навсегда?')
                //             ->class('btn-td')
                //     )
                //     ->align(TD::ALIGN_RIGHT)->class('btn-td-wrap'),

            ]),

            Layout::modal('removeTemplate', Layout::rows([]))->title('Подтвердите удаление'),
        ];
    }

    /**
     * Удаление шаблона
     */
    public function remove(int $id)
    {
        Template::findOrFail($id)->delete();
        Toast::info('Шаблон удалён');
    }
}
