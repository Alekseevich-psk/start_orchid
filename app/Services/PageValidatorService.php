<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Validation\ValidationException;

class PageValidatorService
{
    public function checkTitleUniqueness(string $title, ?int $exceptId = null): void
    {
        $query = Page::where('title', $title);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            $existing = $query->first();
            $this->throwError('page.title', "Страница с таким заголовком уже существует: «{$existing->title}»");
        }
    }

    public function checkAliasUniqueness(string $alias, ?int $exceptId = null): void
    {
        $query = Page::where('alias', $alias);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            $existing = $query->first();
            $this->throwError(
                'page.alias',
                "Адрес <strong>/{$alias}</strong> уже используется: <a href='" .
                    route('platform.page.edit', $existing->id) .
                    "'><em>«{$existing->title}»</em></a>"
            );
        }
    }

    private function throwError(string $field, string $message): void
    {
        throw ValidationException::withMessages([$field => $message])
            ->errorBag('default')
            ->redirectTo(url()->previous());
    }
}
