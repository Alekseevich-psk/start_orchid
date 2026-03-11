<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Template;
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
            ? Page::findOrFail(1)
            : Page::where('slug', $slug)
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->firstOrFail();

        $breadcrumbs = $this->menuService->buildPageBreadcrumbs($page);

        $template = Template::findOrFail($page->template_id);
        $viewPath = $template->path;

        if (!View::exists($viewPath)) {
            abort(404, 'Шаблон не найден');
        }

        return view($viewPath, compact('page', 'breadcrumbs'));
    }
}
