<?php

namespace App\Orchid\Screens;

use App\Models\Feedback;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FeedbackScreen extends Screen
{
    public function query(): array
    {
        return [
            'feedbacks' => Feedback::filters()
                ->defaultSort('created_at', 'desc')
                ->paginate(10),
        ];
    }

    public function name(): ?string
    {
        return 'Сообщения с сайта';
    }

    public function description(): ?string
    {
        return 'Заявки и обращения от пользователей';
    }

    public function commandBar(): array
    {
        return [];
    }

    public function layout(): array
    {
        return [
            Layout::table('feedbacks', [
                TD::make('name', 'Имя'),
                TD::make('sent_at', 'Дата')
                    ->render(function ($model) {
                        if ($model->sent_at) {
                            $date = $model->sent_at->format('Y-m-d');
                            $time = $model->sent_at->format('H:i:s');
                            return "<div>{$date}</div><div>{$time}</div>";
                        }
                        return '';
                    })
                    ->align(TD::ALIGN_CENTER)
                    ->width('120px'),
                TD::make('email', 'Email'),
                TD::make('phone', 'Телефон'),
                TD::make('fields.services', 'Услуги')
                    ->width('280px'),
                // TD::make('fields.form_title', 'Форма'),
                TD::make('actions', 'Действия')
                    ->align(TD::ALIGN_CENTER)
                    ->width('180px')
                    ->render(function (Feedback $feedback) {
                        return
                            ModalToggle::make('Подробнее')
                            ->modal('viewModal')
                            ->modalTitle("Сообщение от {$feedback->name}")
                            ->asyncParameters(['id' => $feedback->id])
                            ->icon('eye')

                            . " "

                            . ModalToggle::make('Удалить')
                            ->modal('deleteModal')
                            ->modalTitle("Подтвердите удаление: {$feedback->name}")
                            ->method('remove')
                            ->asyncParameters(['id' => $feedback->id])
                            ->icon('trash')
                            ->confirm('Вы уверены, что хотите удалить это поле?');
                    })->align(TD::ALIGN_RIGHT),
            ]),

            // Модальное окно: Просмотр данных
            Layout::modal('viewModal', Layout::rows([
                Input::make('feedback.name')
                    ->title('Имя'),

                Input::make('feedback.email')
                    ->title('Email')
                    ->type('email'),

                Input::make('feedback.phone')
                    ->title('Телефон'),

                Input::make('feedback.fields.form_title')
                    ->title('Форма'),

                TextArea::make('feedback.message')
                    ->title('Сообщение')
                    ->rows(4),

                TextArea::make('feedback.fields.services')
                    ->title('Услуги')
                    ->rows(4),
            ]))->title('Просмотр сообщения')->async('asyncGetFeedback')->withoutApplyButton(true),

            // Модальное окно: Удаление
            Layout::modal('deleteModal', [])->title('Подтверждение удаления')->applyButton('Удалить')->method('remove')->closeButton(false),
        ];
    }

    /**
     * Асинхронная загрузка данных для модального окна
     */
    public function asyncGetFeedback(int $id): array
    {
        $feedback = Feedback::findOrFail($id);

        return [
            'feedback' => $feedback->toArray(),
        ];
    }


    public function remove(int $id): void
    {
        Feedback::findOrFail($id)->delete();
        Toast::success('Поле удалено.');
    }
}
