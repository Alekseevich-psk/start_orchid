<?php

namespace App\Orchid\Screens;

use App\Models\Page;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PageScreen extends Screen
{
    public $page;

    public $templates;

    public $children;

    public function query($id = null): array
    {
        $page = $id ? Page::findOrFail($id) : new Page();

        $children = collect();

        if ($page->exists && $page->is_category) {
            $children = Page::where('parent_id', $page->id)
                ->orderBy('menu_order')
                ->orderBy('title')
                ->get();
        }

        return [
            'page'     => $page,
            'templates' => Template::query()->pluck('title', 'id'),
            'children' => $children,
        ];
    }

    public function name(): ?string
    {
        return $this->page->exists
            ? $this->page->title
            : "Создать страницу";
    }

    public function breadcrumbs(): array
    {
        $breadcrumbs = [
            ['title' => 'Панель управления', 'url' => route('platform.main')],
            ['title' => 'Меню сайта', 'url' => route('platform.page.list')]
        ];

        if ($this->page->exists) {
            // Получим всех родителей
            $parent = $this->page;
            $parents = [];
            
            while ($parent && $parent->parent_id) {
                $parent = Page::find($parent->parent_id);
                if ($parent) {
                    $parents[] = $parent;
                }
            }
            
            // Добавляем родителей в обратном порядке
            foreach (array_reverse($parents) as $parent) {
                $breadcrumbs[] = [
                    'title' => $parent->title,
                    'url' => route('platform.page.edit', $parent->id)
                ];
            }
            
            // Текущая страница
            $breadcrumbs[] = ['title' => $this->page->title];
        } else {
            $breadcrumbs[] = ['title' => 'Создать страницу'];
        }
        
        return $breadcrumbs;
    }

    public function commandBar(): array
    {
        $commandBar = [
            Button::make('Сохранить')
                ->icon('check')
                ->method('save')
                ->canSee($this->page->exists),

            Button::make('Создать')
                ->icon('plus')
                ->method('save')
                ->canSee(!$this->page->exists),
        ];

        if ($this->page->exists && $this->page->slug && $this->page->is_published) {
            $commandBar[] = Link::make('Перейти на страницу')
                ->icon('eye-fill')
                ->route('page.show', $this->page->slug)
                ->target('_blank')
                ->canSee(true);
        }

        return $commandBar;
    }

    public function layout(): array
    {
        return [
            Layout::tabs([
                'Дочерние' => $this->page->is_category ? Layout::table('children', [
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
                ]) : [],
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
                        Select::make('page.parent_id')
                            ->fromModel(Page::where('is_category', true)->where('id', '!=', $this->page->id), 'title', 'id')
                            ->empty('Без родителя (корень)', '0')
                            ->title('Родительская страница'),
                    ]),
                    Group::make([
                        Input::make('page.ico')
                            ->title('Иконка')
                            ->help('Оставьте поле пустым, чтобы отобразить иконку шаблона'),
                        Input::make('page.slug')
                            ->title('URL (slug)')
                            ->placeholder('Оставьте пустым — будет сгенерирован автоматически')
                            ->help('Используется в адресе страницы. Только латинские буквы, цифры, дефисы'),
                    ]),
                    Group::make([
                        Switcher::make('page.is_published')
                            ->default(true)
                            ->sendTrueOrFalse()
                            ->title('Опубликовано'),
                        Switcher::make('page.in_menu')
                            ->default(true)
                            ->sendTrueOrFalse()
                            ->title('Отображать в меню'),
                    ]),
                    Group::make([
                        Switcher::make('page.is_category')
                            ->default(false)
                            ->sendTrueOrFalse()
                            ->title('Является категорией'),
                        Switcher::make('page.indexed')
                            ->default(true)
                            ->sendTrueOrFalse()
                            ->title('Индексируется'),
                    ]),
                ]),
                'Блоки' => Layout::view('platform::dummy.block'),
            ]),

            Layout::modal('removeChild', Layout::rows([]))->title('Подтвердите удаление'),
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

        // $data = $this->prepareBooleans($data);
        $data = $this->ensurePublishedAt($data);

        $page->fill($data)->save();

        Toast::info('Страница сохранена');

        return redirect()->route('platform.page.edit', $page->id);
    }

    /**
     * Удаление дочерней страницы
     */
    public function removeChild(int $id)
    {
        $child = Page::findOrFail($id);
        $child->delete();

        Toast::info("Страница «{$child->title}» удалена");
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
