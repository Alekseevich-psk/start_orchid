<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Template;
use App\Services\AttachmentUrlResolver;
use App\Services\MenuService;
use Illuminate\Support\Facades\View;

class PageController extends Controller
{
    protected MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index($slug = null)
    {
        $page = is_null($slug)
            ? Page::published()->findOrFail(1)
            : Page::published()->where('slug', $slug)->first();

        if (!$page) {
            abort(404, 'Страница не найдена');
        }

        $breadcrumbs = $this->menuService->buildBreadcrumbs($page);
        $sections = [];

        if ($page->id === 1) {
            $sections = $this->getContentForIndexPage();
        }

        if ($page->blocks) {
            $page->blocks = (new AttachmentUrlResolver)->resolve($page->blocks);
        }

        $template = Template::findOrFail($page->template_id);
        $viewPath = $template->path;

        if (!$page->is_published) {
            abort(404, 'Страница не найдена');
        }

        if (!View::exists($viewPath)) {
            abort(404, 'Шаблон не найден');
        }

        return view($viewPath, compact('page', 'breadcrumbs', 'sections'));
    }


    private function getContentForIndexPage()
    {
        $data = [];
        return $data;
    }
}
