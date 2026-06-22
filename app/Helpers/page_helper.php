<?php

use App\Models\Page;

if (!function_exists('page_url')) {
    /**
     * Получить URL страницы по ID
     */
    function page_url($id): ?string
    {
        if (!$id) {
            return null;
        }

        $cacheKey = "page.url.{$id}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($id) {
            $page = Page::find($id);
            if (!$page || !$page->is_published) {
                return null;
            }

            return '/' . ltrim($page->slug, '/');
        });
    }
}