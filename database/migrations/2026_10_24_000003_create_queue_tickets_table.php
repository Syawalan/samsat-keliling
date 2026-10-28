<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('queue_tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_schedule_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('queue_number');
            $table->string('tracking_token', 48)->unique();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('service_type', 100);
            $table->string('status', 20)->default('waiting')->index();
            $table->timestamp('issued_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['service_schedule_id', 'queue_number']);
            $table->index(['service_schedule_id', 'status', 'queue_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
    }
};