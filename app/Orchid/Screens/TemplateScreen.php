<?php

namespace App\Orchid\Screens;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TemplateScreen extends Screen
{

    public $template;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query($id = null): array
    {
        $this->template = $id ? Template::findOrFail($id) : new Template();

        $excludedDirs = ['vendor', 'orchid', 'components', 'emails', 'layouts'];

        $bladeFiles = collect(File::allFiles(resource_path('views')))
            ->map(function ($file) {
                $path = str_replace('.blade.php', '', $file->getRelativePathname());
                return str_replace(DIRECTORY_SEPARATOR, '/', $path);
            })
            ->reject(function ($path) use ($excludedDirs) {
                foreach ($excludedDirs as $dir) {
                    if (str_starts_with($path, $dir . '/') || $path === $dir) {
                        return true;
                    }
                }
                return false;
            })
            ->sort()
            ->values()
            ->toArray();

        return [
            'template'   => $this->template,
            'bladeFiles' => $bladeFiles,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->template->exists
            ? $this->template->title
            : "Создать шаблон";
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->icon('check')
                ->method('save')
                ->canSee($this->template->exists),

            Button::make('Создать')
                ->icon('plus')
                ->method('save')
                ->canSee(!$this->template->exists),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        // Получаем bladeFiles из query
        $bladeFiles = $this->query()['bladeFiles'];

        // Преобразуем в ассоциативный массив: ['pages/home' => 'pages/home']
        $options = array_combine($bladeFiles, $bladeFiles);

        return [
            Layout::rows([
                Input::make('template.title')
                    ->title('Название')
                    ->uniqid()
                    ->required(),

                Input::make('template.icon')
                    ->default('file-code')
                    ->title('Иконка'),

                Select::make('template.path')
                    ->title('Шаблон .blade.php')
                    ->placeholder('Не выбрано')
                    ->options($options)
                    ->value($this->template->path) // Устанавливаем текущее значение
                    ->help('Выберите шаблон из папки resources/views'),
            ])
        ];
    }

    public function save(Request $request)
    {
        $pageId = $request->route('id');
        $template = $pageId ? Template::findOrFail($pageId) : new Template();

        $request->validate([
            'template.title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('templates', 'title')->ignore($template->id),
            ],
            'template.path' => 'string|max:255',
        ]);

        $template->fill($request->input('template'))->save();

        Toast::info('Страница сохранена');

        return redirect()->route('platform.template.edit', $template->id);
    }
}
