<?php

namespace App\Orchid\Screens;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SettingListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        return [
            'settings' => Setting::query()
                ->orderBy('group')
                ->orderBy('key')
                ->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Панель конфигурации';
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить')
                ->modal('createSettingModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    /**
     * The screen's layout elements.
     */
    public function layout(): iterable
    {
        return [
            Layout::table('settings', [
                TD::make('title', 'Заголовок')
                    ->render(fn($setting) => $setting->title ?? '<em>Без названия</em>')
                    ->sort(),

                TD::make('key', 'Ключ')
                    ->render(fn($setting) => "<code>{$setting->key}</code>")
                    ->sort(),

                TD::make('value', 'Значение')
                    ->render(fn($setting) => e(Str::limit($setting->value, 50))),

                TD::make('group', 'Группа')
                    ->render(fn($setting) => $setting->group ? e($setting->group) : '<em>-</em>')
                    ->sort()
                    ->filter(Input::make()->placeholder('Фильтр')),

                TD::make('type', 'Тип')
                    ->render(fn($setting) => $setting->type ?? '-'),

                TD::make('actions', 'Действия')
                    ->align(TD::ALIGN_CENTER)
                    ->width('180px')
                    ->render(function (Setting $setting) {
                        return
                            ModalToggle::make('Редактировать')
                            ->modal('editSettingModal')
                            ->modalTitle("Редактировать: {$setting->title}")
                            ->method('update')
                            ->asyncParameters(['id' => $setting->id])
                            ->icon('pencil')

                            . " "

                            . ModalToggle::make('Удалить')
                            ->modal('deleteSettingModal')
                            ->modalTitle("Удалить «{$setting->title}»?")
                            ->method('remove')
                            ->asyncParameters(['id' => $setting->id])
                            ->icon('trash')
                            ->confirm('Вы уверены, что хотите удалить эту настройку?');
                    }),
            ]),

            // Модальное окно: Создание
            Layout::modal('createSettingModal', Layout::rows([
                Input::make('setting.title')
                    ->title('Заголовок')
                    ->placeholder('Например: Режим техработ')
                    ->help('Человекочитаемое название'),

                Input::make('setting.key')
                    ->title('Ключ')
                    ->required()
                    ->placeholder('maintenance_mode')
                    ->help('Только латиница, цифры и подчёркивание'),

                Input::make('setting.value')
                    ->title('Значение')
                    ->type('text')
                    ->placeholder('Введите значение'),

                Input::make('setting.group')
                    ->title('Группа')
                    ->placeholder('system, seo, mail и т.д.')
                    ->help('Для группировки в админке'),

                Input::make('setting.type')
                    ->title('Тип')
                    ->placeholder('text, checkbox, password и т.д.')
                    ->help('Опционально, для фронтенда'),
            ]))->title('Создать настройку')->applyButton('Создать')->async('asyncGetSettingData'),

            // Модальное окно: Редактирование
            Layout::modal('editSettingModal', Layout::rows([
                Input::make('setting.title')
                    ->title('Заголовок')
                    ->placeholder('Например: Режим техработ'),

                Input::make('setting.key')
                    ->title('Ключ')
                    ->disabled() // Запрещаем менять ключ
                    ->help('Ключ нельзя изменить, чтобы не сломать логику'),

                Input::make('setting.value')
                    ->title('Значение')
                    ->type('text')
                    ->required(),

                Input::make('setting.group')
                    ->title('Группа'),

                Input::make('setting.type')
                    ->title('Тип'),
            ]))->title('Редактировать настройку')->applyButton('Сохранить')->async('asyncGetSettingData'),

            // Модальное окно: Удаление
            Layout::modal('deleteSettingModal', [])
                ->title('Подтверждение удаления')
                ->applyButton('Удалить')
                ->method('remove')
                ->closeButton(false),
        ];
    }

    /**
     * Async метод: загружает данные в модальные окна редактирования и удаления
     */
    public function asyncGetSettingData(?int $id = null): array
    {
        $setting = $id ? Setting::findOrFail($id) : new Setting();

        return [
            'setting' => $setting,
        ];
    }

    /**
     * Создать новую настройку
     */
    public function create(Request $request)
    {
        $data = $request->input('setting');

        validator($data, [
            'key'   => 'required|string|unique:settings,key',
            'value' => 'required',
        ])->validate();

        Setting::create($data);

        Toast::success('Настройка создана');
    }

    /**
     * Обновить настройку
     */
    public function update(Request $request, int $id)
    {
        $data = $request->input('setting');

        validator($data, [
            'value' => 'required',
        ])->validate();

        $setting = Setting::findOrFail($id);
        $setting->fill($data)->save();

        Toast::info('Настройка обновлена');
    }

    /**
     * Удалить настройку
     */
    public function remove(int $id)
    {
        $setting = Setting::findOrFail($id);
        $title = $setting->title ?? $setting->key;

        $setting->delete();

        Toast::warning("Настройка «{$title}» удалена");
    }
}
