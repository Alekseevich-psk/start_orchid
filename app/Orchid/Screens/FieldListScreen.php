<?php

namespace App\Orchid\Screens;

use App\Models\Field;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class FieldListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'fields' => Field::filters()
                ->paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Управление полями';
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Создать поле')
                ->modal('createFieldModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('fields', [
                TD::make('field_id', 'Ключ поля'),
                TD::make('title', 'Заголовок'),
                TD::make('type', 'Тип'),
                TD::make('model_type', 'Тип модели'),
                TD::make('model_id', 'ID модели'),

                TD::make('updated_at', 'Обновлено')->sort(),

                TD::make('actions', 'Действия')
                    ->align(TD::ALIGN_CENTER)
                    ->width('150px')
                    ->render(function (Field $field) {
                        return
                            ModalToggle::make('Редактировать')
                            ->modal('editFieldModal')
                            ->modalTitle("Редактировать поле: {$field->title}")
                            ->method('update')
                            ->asyncParameters(['id' => $field->id])
                            ->icon('pencil')

                            . " "

                            . ModalToggle::make('Удалить')
                            ->modal('deleteFieldModal')
                            ->modalTitle("Подтвердите удаление: {$field->title}")
                            ->method('remove')
                            ->asyncParameters(['id' => $field->id])
                            ->icon('trash')
                            ->confirm('Вы уверены, что хотите удалить это поле?');
                    }),
            ]),
            // Модальное окно: Создание
            Layout::modal('createFieldModal', Layout::rows([
                Input::make('field.field_id')
                    ->title('Ключ поля')
                    ->placeholder('Например: title')
                    ->required(),

                Input::make('field.title')
                    ->title('Заголовок')
                    ->placeholder('Например: Главное ')
                    ->required(),

                Input::make('field.description')
                    ->title('Описание')
                    ->placeholder('Доп. информация для пользователя'),

                Select::make('field.type')
                    ->title('Тип поля')
                    ->options([
                        'text'      => 'Текст (text)',
                        'textarea'  => 'Текстовая область',
                        'checkbox'  => 'Флажок',
                        'select'    => 'Выпадающий список',
                        'image'     => 'Изображение',
                        'file'      => 'Файл',
                    ])
                    ->empty('Выберите тип')
                    ->required(),

                Input::make('field.options')
                    ->title('Опции')
                    ->placeholder('Разделитель ||'),

                Input::make('field.model_id')
                    ->title('ID модели')
                    ->type('number')
                    ->value(1)
                    ->required(),

                Select::make('field.model_type')
                    ->title('Тип модели')
                    ->options([
                        'page'     => 'Страница',
                        'template' => 'Шаблон',
                    ])
                    ->value('page')
                    ->required(),
            ]))->title('Создать новое поле')->applyButton('Создать')->async('asyncGetFieldData'),

            // Модальное окно: Редактирование
            Layout::modal('editFieldModal', Layout::rows([
                Input::make('field.field_id')
                    ->title('Ключ поля')
                    ->required(),

                Input::make('field.title')
                    ->title('Заголовок')
                    ->required(),

                Input::make('field.description')
                    ->title('Описание')
                    ->placeholder('Доп. информация для пользователя'),

                Select::make('field.type')
                    ->title('Тип поля')
                    ->options([
                        'text'      => 'Текст (text)',
                        'textarea'  => 'Текстовая область',
                        'checkbox'  => 'Флажок',
                        'select'    => 'Выпадающий список',
                        'image'     => 'Изображение',
                        'file'      => 'Файл',
                    ])
                    ->required(),
                    
                Input::make('field.options')
                    ->title('Опции')
                    ->placeholder('Разделитель ||'),

                Input::make('field.model_id')
                    ->title('ID модели')
                    ->type('number')
                    ->required(),

                Select::make('field.model_type')
                    ->title('Тип модели')
                    ->options([
                        'page'     => 'Страница',
                        'template' => 'Шаблон',
                    ])
                    ->required(),
            ]))->title('Редактировать поле')->applyButton('Сохранить')->async('asyncGetFieldData'),

            // Модальное окно: Удаление
            Layout::modal('deleteFieldModal', [])->title('Подтверждение удаления')->applyButton('Удалить')->method('remove')->closeButton(false),
        ];
    }

    /**
     * Async метод для заполнения данных в модальных окнах.
     */
    public function asyncGetFieldData(?int $id = null): array
    {
        $field = $id
            ? Field::findOrFail($id)
            : new Field();

        return [
            'field' => $field
        ];
    }

    public function create(Request $request): void
    {
        Field::create($request->input('field'));
        Toast::info('Поле успешно создано.');
    }

    public function update(Request $request, int $id): void
    {
        $field = Field::findOrFail($id);
        $field->fill($request->input('field'))->save();
        Toast::info('Поле обновлено.');
    }

    public function remove(int $id): void
    {
        Field::findOrFail($id)->delete();
        Toast::success('Поле удалено.');
    }
}
