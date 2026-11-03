<?php

namespace App\Models;

use Database\Factories\MissionPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int|null $mission_id
 * @property string|null $caption
 * @property string|null $location
 * @property string $image_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['mission_id', 'caption', 'location', 'image_path'])]
class MissionPhoto extends Model
{
    /** @use HasFactory<MissionPhotoFactory> */
    use HasFactory;

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * Get the publicly accessible URL of the stored image.
     */
    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
