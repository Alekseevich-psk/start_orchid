<?php

namespace App\Services;

use Orchid\Attachment\Models\Attachment;
use Illuminate\Support\Arr;

class AttachmentUrlResolver
{
    protected array $urlMap = [];
    protected bool $loaded = false;

    /**
     * Извлекает все ID из данных и загружает URL одним запросом
     */
    public function resolve(array $data): array
    {
        $this->collectAndLoadUrls($data);
        $this->replaceIdsWithUrls($data);

        return $data;
    }

    /**
     * Собрать все ID и загрузить мап id → url
     */
    private function collectAndLoadUrls(array $data): void
    {
        if ($this->loaded) return;

        $ids = $this->collectIds($data);
        if (empty($ids)) {
            $this->loaded = true;
            return;
        }

        $this->urlMap = Attachment::whereIn('id', $ids)
            ->get()
            ->pluck('url', 'id')
            ->map(fn($url) => (string)$url)
            ->toArray();

        $this->loaded = true;
    }

    /**
     * Рекурсивно найти все file_* и files_* поля
     */
    private function collectIds(array $data, array &$ids = []): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Собираем ID из file_* и files_*
                if ($this->isFileField($key) && $this->looksLikeIds($value)) {
                    foreach ($value as $id) {
                        if ($id !== null) {
                            $ids[] = $id;
                        }
                    }
                }

                // Рекурсия
                $this->collectIds($value, $ids);
            }
        }

        return array_unique($ids);
    }

    /**
     * Заменяет ID на URL в тех же полях
     */
    private function replaceIdsWithUrls(&$data): void
    {
        if (!is_array($data)) {
            return;
        }

        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                // Заменяем содержимое file_* / files_*
                if ($this->isFileField($key) && $this->looksLikeIds($value)) {
                    $urls = $this->mapIdsToUrls($value);
                    $value = count($urls) === 1 ? $urls[0] : $urls;
                }

                // Обработка rm_* и других вложенных
                $this->replaceIdsWithUrls($value);
            }
        }
        unset($value);
    }

    /**
     * Проверка: ключ файловый?
     */
    private function isFileField(string $key): bool
    {
        return str_starts_with($key, 'file_') || str_starts_with($key, 'files_');
    }

    /**
     * Похоже ли значение на массив ID?
     */
    private function looksLikeIds(mixed $value): bool
    {
        return is_array($value) && !Arr::isAssoc($value) && count($value) > 0;
    }

    /**
     * Преобразует ID в URL, используя кэш
     */
    private function mapIdsToUrls(array $ids): array
    {
        return collect($ids)
            ->map(fn($id) => $this->urlMap[$id] ?? null)
            ->filter()
            ->values()
            ->toArray();
    }
}
