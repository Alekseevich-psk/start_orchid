<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Template;
use Illuminate\Support\Facades\View;
// use Illuminate\Http\Request;

class PageController extends Controller
{

    public function show($slug = null)
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

        $template = Template::findOrFail($page->template_id);
        $viewPath = $template->path;

        if (!View::exists($viewPath)) {
            abort(404, 'Шаблон не найден');
        }

        return view($viewPath, compact('page'));
    }
}
