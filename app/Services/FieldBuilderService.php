<?php

namespace App\Services;

use App\Models\Field;
use App\Models\Page;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;

class FieldBuilderService
{
    public function build(Page $page): array
    {
        $formFields = [];
        $fields = collect();

        // Поля из шаблона
        if ($page->template_id) {
            $templateFields = Field::where('model_type', 'template')
                ->where('model_id', $page->template_id)
                ->get();
            $fields = $fields->concat($templateFields);
        }

        // Поля из страницы (имеют приоритет)
        if ($page->id) {
            $pageFields = Field::where('model_type', 'page')
                ->where('model_id', $page->id)
                ->get();

            $fields = $fields->reject(fn($field) => $pageFields->contains('field_id', $field->field_id))
                ->concat($pageFields);
        }

        foreach ($fields as $field) {
            $name = "page.blocks.{$field->field_id}";

            switch ($field->type) {
                case 'text':
                    $formFields[] = Input::make($name)->title($field->title);
                    break;
                case 'textarea':
                    $formFields[] = TextArea::make($name)->title($field->title)->rows(5);
                    break;
                case 'checkbox':
                    $formFields[] = CheckBox::make($name)->title($field->title);
                    break;
                case 'select':
                    $options = $this->parseOptions($field->options);
                    $formFields[] = Select::make($name)->title($field->title)->options($options);
                    break;
                case 'image':
                    $formFields[] = Picture::make($name)->title($field->title)->maxFiles(1);
                    break;
                case 'file':
                    $formFields[] = Upload::make($name)->title($field->title)->maxFiles(1);
                    break;
                default:
                    $formFields[] = Input::make($name)
                        ->title("{$field->title} ({$field->type})")
                        ->help('Тип не поддерживается');
                    break;
            }
        }

        return $formFields;
    }

    private function parseOptions(?string $raw): array
    {
        if (!$raw) return [];

        return collect(explode('||', $raw))
            ->map(fn($opt) => trim($opt))
            ->filter()
            ->mapWithKeys(fn($opt) => [$opt => $opt])
            ->toArray();
    }
}
