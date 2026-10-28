<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['service_date', 'location', 'starts_at', 'ends_at', 'quota', 'service_minutes', 'info_title', 'info_content', 'is_active'])]
class ServiceSchedule extends Model
{
    protected function casts(): array
    {
        return ['service_date' => 'date', 'is_active' => 'boolean'];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }
}