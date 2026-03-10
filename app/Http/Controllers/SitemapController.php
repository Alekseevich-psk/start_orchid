<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;


class SitemapController extends Controller
{
    public function index(MenuService $menuService): Response
    {
        $menuTree = $menuService->getFrontendMenuTree();
        
        $items = [];
        $flatten = function ($tree) use (&$flatten, &$items) {
            foreach ($tree as $item) {
                // Только индексируемые страницы
                if (!isset($item['indexed']) || $item['indexed'] === true) {
                    $items[] = [
                        'loc' => $item['url'],
                        'lastmod' => now()->format('c'),
                        'changefreq' => 'weekly',
                        'priority' => '0.8'
                    ];
                }
                
                if (isset($item['children']) && is_array($item['children'])) {
                    $flatten($item['children']);
                }
            }
        };
        
        $flatten($menuTree);
        
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        
        foreach ($items as $item) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>{$item['loc']}</loc>\n";
            $xml .= "        <lastmod>{$item['lastmod']}</lastmod>\n";
            $xml .= "        <changefreq>{$item['changefreq']}</changefreq>\n";
            $xml .= "        <priority>{$item['priority']}</priority>\n";
            $xml .= "    </url>\n";
        }
        
        $xml .= "</urlset>";
        
        return response($xml)->header('Content-Type', 'application/xml');
    }
}
