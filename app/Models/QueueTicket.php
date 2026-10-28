<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['service_schedule_id', 'queue_number', 'tracking_token', 'name', 'phone', 'service_type', 'status', 'issued_at', 'called_at', 'completed_at'])]
class QueueTicket extends Model
{
    protected static function booted(): void
    {
        static::creating(function (QueueTicket $ticket): void {
            $ticket->tracking_token ??= Str::random(48);
        });
    }

    protected function casts(): array
    {
        return ['issued_at' => 'datetime', 'called_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    protected function displayNumber(): Attribute
    {
        return Attribute::get(fn (): string => 'A' . str_pad((string) $this->queue_number, 3, '0', STR_PAD_LEFT));
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ServiceSchedule::class, 'service_schedule_id');
    }
}