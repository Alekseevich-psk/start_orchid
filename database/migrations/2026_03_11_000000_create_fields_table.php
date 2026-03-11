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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_id');
            $table->string('title');
            $table->string('type');
            $table->string('model_type')->default('page'); // Page or Template
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the Page or Template
            $table->timestamps();
            $table->unique(['field_id', 'model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};