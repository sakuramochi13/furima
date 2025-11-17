<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_image_url',
        'postal_code',
        'address',
        'building',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        $path = $this->profile_image_url;

        if (!$path) {
            return asset('images/default-user.svg');
        }

        $path = ltrim($path, '/');

        if (strpos($path, 'storage/') === 0) {
            return asset($path);
        }

        if (strpos($path, 'profiles/') === 0) {
            return asset('storage/' . $path);
        }

        return asset('storage/profiles/' . $path);
    }
}
