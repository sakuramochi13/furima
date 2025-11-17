<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_id',
        'product_name',
        'description',
        'price',
        'condition',
        'status',
        'image_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function isLikedBy(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeNameContains($query, ?string $keyword)
    {
        $kw = trim((string)$keyword);
        if ($kw === '') return $query;

        $escaped = addcslashes($kw, '\\%_');
        return $query->where('product_name', 'LIKE', "%{$escaped}%");
    }

    public function scopeNotOwnedBy($query, ?int $userId)
    {
        return $userId ? $query->where('user_id', '!=', $userId) : $query;
    }

    public const CONDITION = [
        'excellent'  => '良好',
        'very_good'  => '目立った傷や汚れなし',
        'good'       => 'やや傷や汚れあり',
        'poor'       => '状態が悪い',
    ];

    public function getConditionLabelAttribute(): string
    {
        return self::CONDITION[$this->condition];
    }

    public function getImageUrlAttribute($value)
    {
        if (Str::startsWith($value, '/storage/')) {
            return $value;
        }
        return asset('storage/' . $value);
    }
}


