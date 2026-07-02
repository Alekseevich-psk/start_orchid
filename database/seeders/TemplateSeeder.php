<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Template::firstOrCreate([
            'title'       => 'Главная',
            'path'  => 'index',
        ]);

        Template::firstOrCreate([
            'title'       => 'Контакты',
            'path'  => 'contacts',
        ]);

        Template::firstOrCreate([
            'title'       => 'Документ',
            'path'  => 'document',
        ]);
    }
}
