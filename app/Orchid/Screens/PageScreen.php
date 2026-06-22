<?php

namespace App\Orchid\Screens;

use App\Models\Field;
use App\Models\Page;
use App\Models\Template;
use App\Services\FieldBuilderService;
use App\Services\MenuService;
use App\Services\PageValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Attach;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
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
            'page' => $page,
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

    public function commandBar(): array
    {

        $commandBar = [];

        if ($this->page->is_category) {
            $commandBar[] = Link::make('Добавить дочернюю страницу')
                ->method('GET')
                ->icon('plus')
                ->route('platform.page.create', [
                    'parent_id' => $this->page->id,
                    'template_child_id' => $this->page->template_child_id,
                ]);
        }

        $commandBar[] = Button::make('Сохранить')
            ->icon('check')
            ->method('save')
            ->canSee($this->page->exists)
            ->shortcut('s');

        $commandBar[] = Button::make('Создать')
            ->icon('plus')
            ->method('save')
            ->shortcut('n')
            ->canSee(!$this->page->exists);

        if ($this->page->exists && $this->page->alias && $this->page->is_published) {
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
        $tabs = [
            'Дочерние' => $this->page->is_category ? Layout::table('children', [
                TD::make('id', 'id')->sort(),
                TD::make('title', 'Заголовок')
                    ->render(
                        fn($page) =>
                        Link::make($page->title)
                            ->route('platform.page.edit', $page->id)
                            ->class('text-dark td-title text-decoration-none')
                    ),
                TD::make('alias', 'Псевдоним'),
                TD::make('')
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
                            ->modal('removeChild')
                            ->modalTitle("Удалить \"{$page->title}\"?")
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
                    ->height('620px')
                    ->toolbar(['text', 'header', 'color', 'quote', 'header', 'list', 'format', 'media'])
                    ->label('Контент'),
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
                        ->fromModel(Template::class, 'title', 'id')
                        ->title('Шаблон ресурса')
                        ->value(
                            $this->page->exists
                                ? $this->page->template_child_id
                                : (request('template_child_id') ?? 4) // значение по умолчанию — 4
                        ),
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
                        ->title('Родительская страница')
                        ->value($this->page->exists ? $this->page->parent_id : request('parent_id')),
                ]),
                Group::make([
                    Input::make('page.ico')
                        ->title('Иконка')
                        ->help('Оставьте поле пустым, чтобы отобразить иконку шаблона'),
                    Input::make('page.alias')
                        ->title('Псевдоним')
                        ->placeholder('Оставьте пустым — будет сгенерирован автоматически')
                        ->help('Используется в адресе страницы. Только латинские буквы, цифры, дефисы'),
                ]),
                Group::make([
                    Input::make('page.ico')
                        ->title('Иконка')
                        ->help('Оставьте поле пустым, чтобы отобразить иконку шаблона'),
                    Select::make('page.template_child_id')
                        ->fromModel(Template::class, 'title', 'id')
                        ->title('Шаблон дочерних ресурсов')
                        ->value($this->page->exists ? $this->page->template_child_id : request('template_child_id')),
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
                Group::make([
                    Switcher::make('page.in_slug_path')
                        ->default(true)
                        ->sendTrueOrFalse()
                        ->title('Участвует в формировании url дочерних страниц'),
                ]),
                Group::make([
                    Attach::make('page.image')
                        ->title('Превью страницы')
                        ->width(500)
                        ->height(300)
                        ->targetRelativeUrl()
                        ->required(false),
                ]),
            ]),
        ];

        // Добавляем вкладку "Блоки" только если есть поля
        $customFields = app(FieldBuilderService::class)->build($this->page);

        if (!empty($customFields)) {
            $tabs['Блоки'] = Layout::rows($customFields);
        }

        return [
            Layout::tabs($tabs),

            Layout::modal('removeChild', Layout::rows([]))->title('Подтвердите удаление'),
                // Layout::modal('removePage', [])->title('Подтверждение удаления')->applyButton('Удалить')->method('remove')->closeButton(false),
        ];
    }

    public function save(Request $request)
    {
        $page = $this->getPageFromRequest($request);
        $data = $request->input('page');

        $this->validateInput($data, $page);
        $data = $this->prepareAlias($data);

        app(PageValidatorService::class)->checkTitleUniqueness($data['title'], $page->id);
        app(PageValidatorService::class)->checkAliasUniqueness($data['alias'], $page->id);

        $data = $this->ensurePublishedAt($data);

        // Генерируем slug через сервис
        $data['slug'] = app(MenuService::class)->generateFullPath(
            $data['alias'],
            $data['parent_id'] ?? null,
            $page->id ?? null
        );

        $page->fill($data)->save();

        $page->attachments()->sync(
            $request->input('picture', []),
            'image'
        );

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
            'alias'  => 'nullable|string|max:255',
        ])->validate();
    }

    /**
     * Генерация alias, если не задан.
     */
    private function prepareAlias(array $data): array
    {
        if (empty($data['alias'])) {
            $data['alias'] = app(MenuService::class)->generateAlias(
                $data['title'] ?? '',
                $this->page->id ?? null
            );
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
     * Удаление шаблона
     */
    public function remove(int $id)
    {
        Page::findOrFail($id)->delete();
        Toast::info('Страница удалена!');
    }
}
