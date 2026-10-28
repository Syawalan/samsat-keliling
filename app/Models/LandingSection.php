<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'icon', 'title', 'description', 'subheading', 'subheading_text', 'items_title', 'items', 'sort_order', 'is_active'])]
class LandingSection extends Model
{
    protected function casts(): array
    {
        return ['items' => 'array', 'is_active' => 'boolean'];
    }
}