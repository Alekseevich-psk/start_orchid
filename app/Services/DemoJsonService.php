<?php

namespace App\Services;

use App\Services\AttachmentUrlResolver;
use Illuminate\Support\Facades\Cache;

class DemoJsonService
{
    protected $data;

    public function __construct()
    {
        $this->loadData();
    }

    /**
     * Загружаем данные из JSON
     */
    protected function loadData(): void
    {
        $jsonPath = base_path('resources/views/data/data.json');

        $this->data = Cache::remember('site.data', 3600, function () use ($jsonPath) {
            $rawData = json_decode(file_get_contents($jsonPath), true);

            if ($rawData === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Ошибка при декодировании JSON: ' . json_last_error_msg());
            }

            return (new AttachmentUrlResolver)->resolve($rawData);
        });
    }

    /**
     * Получить значение по ключу
     */
    public function get(string $key, $default = null)
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Получить все данные
     */
    public function all(): array
    {
        return $this->data;
    }
}
