<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class File extends Model
{
    protected $fillable = [
        'name',
        'path',
        'mime_type',
        'size',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function scopeSizeBetween(Builder $query, $value): void
    {
        if (is_string($value) && str_contains($value, ',')) {
            [$min, $max] = explode(',', $value);
            $query->whereBetween('size', [(int)$min, (int)$max]);
        }
    }

    public function scopeCreatedAfter(Builder $query, $value): void
    {
        $query->where('created_at', '>=', $value);
    }

    public function scopeCreatedBefore(Builder $query, $value): void
    {
        $query->where('created_at', '<=', $value);
    }
}
