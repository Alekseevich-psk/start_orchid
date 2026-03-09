<?php

namespace App\Services;

use App\Models\Page;

class MenuService
{
    public function buildTree($items, $parentId = 0)
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item->parent == $parentId) {
                $children = $this->buildTree($items, $item->id);

                if ($children) {
                    $item->children = $children;
                }

                $branch[] = $item;
            }
        }

        return $branch;
    }

    public function getPublishedMenuItems()
    {
        $pages = Page::inMenu()->orderBy('menu_order')->get();

        return $this->buildTree($pages);
    }
}
