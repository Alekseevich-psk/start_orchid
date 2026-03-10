<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Template;
use Illuminate\Support\Facades\View;
// use Illuminate\Http\Request;

class PageController extends Controller
{

    public function index($slug = null)
    {
        if (is_null($slug)) {
            $page = Page::findOrFail(1); // Главная — всегда id 1
        } else {
            $page = Page::where('slug', $slug)
                ->where('is_published', true)
                ->where(function ($query) {
                    $query->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->firstOrFail();
        }

        // Построение хлебных крошек
        $breadcrumbs = [];
        $current = $page;
        while ($current && $current->parent_id) {
            $current = Page::find($current->parent_id);
            if ($current) {
                $breadcrumbs[] = [
                    'title' => $current->title,
                    'url' => route('page.show', $current->slug)
                ];
            }
        }
        // Корень в начало
        $breadcrumbs = array_reverse($breadcrumbs);
        // Текущая страница
        $breadcrumbs[] = ['title' => $page->title];

        $template = Template::findOrFail($page->template_id);
        $viewPath = $template->path;

        if (!View::exists($viewPath)) {
            abort(404, 'Шаблон не найден');
        }

        return view($viewPath, compact('page', 'breadcrumbs'));
    }
}
