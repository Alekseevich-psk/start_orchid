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
            'title'       => 'default',
            'path'  => 'resources/views/welcome.blade.php',
        ]);
    }
}
