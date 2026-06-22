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
            'path'  => 'resources/views/index.blade.php',
        ]);

        Template::firstOrCreate([
            'title'       => 'Контакты',
            'path'  => 'resources/views/contacts.blade.php',
        ]);
    }
}
