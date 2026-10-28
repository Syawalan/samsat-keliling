<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('landing_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('icon', 16)->default('ℹ️');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('subheading')->nullable();
            $table->text('subheading_text')->nullable();
            $table->string('items_title')->nullable();
            $table->json('items')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_sections');
    }
};