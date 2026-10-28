<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_schedules', function (Blueprint $table): void {
            $table->id();
            $table->date('service_date')->unique();
            $table->string('location');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedSmallInteger('quota')->default(60);
            $table->unsignedTinyInteger('service_minutes')->default(5);
            $table->string('info_title')->default('Layanan Samsat Keliling');
            $table->text('info_content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_schedules');
    }
};