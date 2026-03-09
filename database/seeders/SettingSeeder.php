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
                'key' => 'site_slogan',
                'value' => 'Краткий слоган или подзаголовок',
                'group' => 'general',
                'type' => 'text',
                'title' => 'Слоган',
            ],
            [
                'key' => 'site_email',
                'value' => 'info@yoursite.com',
                'group' => 'general',
                'type' => 'text',
                'title' => 'Email для связи',
            ],
            [
                'key' => 'site_phone',
                'value' => '+7 (XXX) XXX-XX-XX',
                'group' => 'general',
                'type' => 'text',
                'title' => 'Телефон',
            ],
            [
                'key' => 'site_logo',
                'value' => '',
                'group' => 'general',
                'type' => 'image',
                'title' => 'Логотип',
            ],
            [
                'key' => 'seo_title',
                'value' => 'Мой сайт — всё о чём-то важном',
                'group' => 'seo',
                'type' => 'text',
                'title' => 'Meta Title по умолчанию',
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
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/ваш_профиль',
                'group' => 'social',
                'type' => 'text',
                'title' => 'Facebook',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/ваш_профиль',
                'group' => 'social',
                'type' => 'text',
                'title' => 'Instagram',
            ],
            [
                'key' => 'social_vk',
                'value' => 'https://vk.com/ваш_профиль',
                'group' => 'social',
                'type' => 'text',
                'title' => 'ВКонтакте',
            ],
            [
                'key' => 'social_telegram',
                'value' => 'https://t.me/ваш_канал',
                'group' => 'social',
                'type' => 'text',
                'title' => 'Telegram',
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
            [
                'key' => 'address',
                'value' => 'г. Москва, ул. Примерная, д. 1',
                'group' => 'contacts',
                'type' => 'textarea',
                'title' => 'Адрес',
            ],
            [
                'key' => 'work_hours',
                'value' => 'Пн-Пт: 9:00–18:00',
                'group' => 'contacts',
                'type' => 'text',
                'title' => 'Часы работы',
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
