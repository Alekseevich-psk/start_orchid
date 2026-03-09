<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageHomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::firstOrCreate([
            'slug' => '/',
        ], [
            'title'         => 'Главная',
            'content'       => '<p>Добро пожаловать на главную страницу!</p>',
            'menu_order'    => 1,
            'template_id'   => 1, 
            'parent'        => 0,
            'in_menu'       => true,
            'is_published'  => true,
            'is_category'   => false, 
            'type'          => 'page'
        ]);
    }
}