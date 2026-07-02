<?php

namespace App\Services;

use App\Services\AttachmentUrlResolver;

class DemoJsonService
{
    protected $data;

    public function __construct()
    {
        $this->loadData();
    }

    /**
     * Загружаем данные из JSON без кэширования
     */
    protected function loadData(): void
    {
        $jsonPath = base_path('resources/views/data/data.json');

        if (!file_exists($jsonPath)) {
            throw new \RuntimeException('JSON файл не найден: ' . $jsonPath);
        }

        $rawData = json_decode(file_get_contents($jsonPath), true);

        if ($rawData === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Ошибка при декодировании JSON: ' . json_last_error_msg());
        }

        $this->data = (new AttachmentUrlResolver)->resolve($rawData);
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
