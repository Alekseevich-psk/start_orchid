<?php


namespace Database\Seeders;

use App\Models\Field;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Field::create([
            'field_id' => 'title',
            'title' => 'Тестовое поле',
            'type' => 'input',
            'model_type' => 'page',
            'model_id' => 1,
        ]);
    }
}
