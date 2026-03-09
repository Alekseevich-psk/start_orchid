<?php

namespace App\Orchid\Screens;

use App\Models\Page;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PageScreen extends Screen
{
    public $page;

    public $templates;

    public function query($id = null): array
    {
        return [
            'page' => $id
                ? Page::findOrFail($id)
                : new Page(),
            'templates' => Template::query()->pluck('title', 'id')
        ];
    }

    public function name(): ?string
    {
        return $this->page->exists
            ? $this->page->title
            : "Создать страницу";
    }

    public function commandBar(): array
    {
        return [
            Button::make('Сохранить')
                ->icon('check')
                ->method('save')
                ->canSee($this->page->exists),

            Link::make('Перейти на страницу')
                ->icon('eye-fill')
                ->route('page.show', $this->page->slug)
                ->target('_blank')
                ->canSee($this->page->exists),

            Button::make('Создать')
                ->icon('plus')
                ->method('save')
                ->canSee(!$this->page->exists),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::tabs([
                'Контент' => Layout::rows([
                    Input::make('page.title')
                        ->title('Заголовок')
                        ->required(),
                    Input::make('page.subtitle')
                        ->title('Расширенный заголовок'),
                    Input::make('page.excerpt')
                        ->title('Аннотация'),
                    TextArea::make('page.description')
                        ->title('Описание (SEO)'),
                    Quill::make('page.content')
                        ->title('Контент'),
                    Cropper::make('page.image')
                        ->title('Превью страницы')
                        ->width(500)
                        ->height(300),
                ]),
                'Настройки' => Layout::rows([
                    Group::make([
                        Select::make('page.type')
                            ->title('Тип страницы')
                            ->options([
                                'page' => 'Страница',
                                'xml' => 'XML',
                                'link' => 'Ссылка',
                            ]),
                        Select::make('page.template_id')
                            ->title('Шаблон страницы')
                            ->options($this->templates),
                    ]),
                    Group::make([
                        DateTimer::make('page.published_at')
                            ->title('Дата публикации')
                            ->allowEmpty(),
                        DateTimer::make('page.unpublished_at')
                            ->type('datetime')
                            ->title('Дата окончания публикации')
                            ->allowEmpty(),
                    ]),
                    Group::make([
                        Input::make('page.menu_order')
                            ->type('number')
                            ->title('Порядок в меню')
                            ->value(0),
                        Select::make('page.parent')
                            ->fromModel(Page::class, 'title', 'id')
                            ->empty('Без родителя (корень)', '0')
                            ->title('Родительская страница'),
                    ]),
                    Group::make([
                        Switcher::make('page.is_published')
                            ->title('Опубликовано'),
                        Switcher::make('page.in_menu')
                            ->title('Отображать в меню'),
                    ]),
                    Group::make([
                        Switcher::make('page.is_category')
                            ->title('Является категорией'),
                        Switcher::make('page.indexed')
                            ->title('Индексируется'),
                    ]),
                    Input::make('page.slug')
                        ->title('URL (slug)')
                        ->placeholder('Оставьте пустым — будет сгенерирован автоматически')
                        ->help('Используется в адресе страницы. Только латинские буквы, цифры, дефисы'),
                ]),
                'Блоки' => Layout::view('platform::dummy.block'),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $page = $this->getPageFromRequest($request);
        $data = $request->input('page');

        $this->validateInput($data, $page);
        $data = $this->prepareSlug($data);

        $this->checkTitleUniqueness($data, $page);
        $this->checkSlugUniqueness($data, $page);

        $data = $this->prepareBooleans($data);
        $data = $this->ensurePublishedAt($data);

        $page->fill($data)->save();

        Toast::info('Страница сохранена');

        return redirect()->route('platform.page.edit', $page->id);
    }

    /**
     * Получить страницу по ID или создать новую.
     */
    private function getPageFromRequest(Request $request): Page
    {
        $pageId = $request->route('id');
        return $pageId ? Page::findOrFail($pageId) : new Page();
    }

    /**
     * Валидация базовых полей.
     */
    private function validateInput(array $data): void
    {
        validator($data, [
            'title' => 'required|string|max:255',
            'slug'  => 'nullable|string|max:255',
        ])->validate();
    }

    /**
     * Генерация slug, если не задан.
     */
    private function prepareSlug(array $data): array
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title'] ?? 'page');
        }
        return $data;
    }

    /**
     * Проверка уникальности заголовка.
     */
    private function checkTitleUniqueness(array $data, Page $page): void
    {
        $existing = Page::where('title', $data['title'])
            ->when($page->id, fn($q) => $q->where('id', '!=', $page->id))
            ->first();

        if ($existing) {
            $this->throwValidationError(
                'page.title',
                "Страница с таким заголовком уже существует: «{$existing->title}»"
            );
        }
    }

    /**
     * Проверка уникальности slug.
     */
    private function checkSlugUniqueness(array $data, Page $page): void
    {
        $existing = Page::where('slug', $data['slug'])
            ->when($page->id, fn($q) => $q->where('id', '!=', $page->id))
            ->first();

        if ($existing) {
            $this->throwValidationError(
                'page.slug',
                "Адрес <strong>/{$data['slug']}</strong> уже используется: <a href='" .
                    route('platform.page.edit', $existing->id) .
                    "'><em>«{$existing->title}»</em></a>"
            );
        }
    }

    /**
     * Преобразование строковых значений в boolean.
     */
    private function prepareBooleans(array $data): array
    {
        $booleans = ['is_published', 'in_menu', 'is_category', 'indexed'];
        foreach ($booleans as $field) {
            $data[$field] = filter_var($data[$field] ?? false, FILTER_VALIDATE_BOOLEAN);
        }
        return $data;
    }

    /**
     * Авто установка published_at при публикации.
     */
    private function ensurePublishedAt(array $data): array
    {
        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        return $data;
    }

    /**
     * Бросаем ошибку с подсветкой поля и HTML-сообщением.
     */
    private function throwValidationError(string $field, string $message): void
    {
        throw ValidationException::withMessages([
            $field => $message,
        ])->errorBag('default')->redirectTo(url()->previous());
    }
}
