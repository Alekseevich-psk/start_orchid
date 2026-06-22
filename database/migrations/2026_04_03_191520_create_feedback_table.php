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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

            // Общая информация
            $table->string('type')->default('feedback')->index(); // feedback, callback, order...
            $table->string('source')->nullable()->index();       // site, landing, partner
            $table->string('form_id')->nullable()->index();      // ID конкретной формы

            // Контактные данные
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('message')->nullable();

            // Динамические поля (JSON)
            $table->json('fields')->nullable();  // Для любых дополнительных полей формы
            $table->json('meta')->nullable();   // Для системных данных (device, screen и т.п.)

            // Информация о пользователе
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Технические данные
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referral')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();

            // Статус и обработка
            $table->string('status')->default('new')->index(); // new, processing, done, spam
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('sent_at')->nullable()->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Для автоматической очистки

            // Аудит
            $table->timestamps(); // created_at, updated_at
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
