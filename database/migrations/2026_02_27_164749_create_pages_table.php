<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');                                // Заголовок
            $table->string('subtitle')->nullable();                 // Подзаголовок
            $table->text('description')->nullable();                // SEO описание
            $table->text('excerpt')->nullable();                    // Аннотация
            $table->string('image')->nullable();                    // Превью изображение
            $table->text('content')->nullable();                    // Основной контент (HTML)
            $table->json('blocks')->nullable();                     // Блоки: слайдеры, отзывы и т.д.
            $table->string('type')->default('page');                // Тип страницы
            $table->boolean('is_category')->default(false);         // Можно как категория
            $table->boolean('indexed')->default(true);              // Индексация страницы
            $table->integer('parent')->default(0);                  // id родителя
            $table->string('slug')->unique();                       // URL
            $table->boolean('is_published')->default(false);        // Опубликовано
            $table->unsignedBigInteger('template_id')->nullable();  // Шаблон
            $table->boolean('in_menu')->default(false);             // В меню
            $table->integer('menu_order')->default(0);              // Порядок в меню
            $table->string('alias')->nullable()->unique();          // Псевдоним / алиас
            $table->timestamp('published_at')->nullable();          // Дата публикации
            $table->timestamp('unpublished_at')->nullable();        // Дата окончания публикации
            $table->json('allowed_roles')->nullable();              // Роли с доступом
            $table->timestamps();

            // Индексы для ускорения запросов
            $table->index('type');
            $table->index('is_published');
            $table->index('in_menu');
            $table->index('published_at');
            $table->index('unpublished_at');
            $table->index('template_id');
            $table->index('parent');
            $table->index('alias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
