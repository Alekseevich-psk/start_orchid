<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'Мой сайт',
                'group' => 'general',
                'type' => 'text',
                'title' => 'Название сайта',
            ],
            [
                'key' => 'seo_description',
                'value' => 'Описание сайта для поисковых систем.',
                'group' => 'seo',
                'type' => 'textarea',
                'title' => 'Meta Description по умолчанию',
            ],
            [
                'key' => 'seo_keywords',
                'value' => 'ключевые, слова, через, запятую',
                'group' => 'seo',
                'type' => 'text',
                'title' => 'Ключевые слова (keywords)',
            ],
            [
                'key' => 'fancy_urls',
                'value' => '1',
                'group' => 'seo',
                'type' => 'boolean',
                'title' => 'Включить ЧПУ',
            ],
            [
                'key' => 'generate_sitemap',
                'value' => '1',
                'group' => 'seo',
                'type' => 'boolean',
                'title' => 'Автогенерация sitemap.xml',
            ],
            [
                'key' => 'google_analytics_id',
                'value' => '',
                'group' => 'analytics',
                'type' => 'text',
                'title' => 'Google Analytics ID (G-XXXXX)',
            ],
            [
                'key' => 'yandex_metrika_id',
                'value' => '',
                'group' => 'analytics',
                'type' => 'text',
                'title' => 'Яндекс.Метрика ID',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'group' => 'system',
                'type' => 'boolean',
                'title' => 'Режим обслуживания',
            ],
            [
                'key' => 'cache_enabled',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'title' => 'Кэширование включено',
            ],
            [
                'key' => 'debug_mode',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'title' => 'Режим отладки',
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::firstOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }
    }
}
