<?php

namespace App\Models;

use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property Carbon $date
 * @property Carbon $time_from
 * @property Carbon $time_to
 * @property string $type
 * @property string|null $other_type
 * @property string|null $image
 * @property string|null $venue
 * @property string|null $link
 * @property string|null $meeting_id
 * @property string|null $passcode
 * @property int|null $mission_id
 * @property array|null $reports
 * @property string|null $report_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'description', 'date', 'time_from', 'time_to', 'type', 'other_type', 'image', 'venue', 'link', 'meeting_id', 'passcode', 'reports', 'report_name', 'mission_id'])]
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'datetime:Y-m-d',
            'time_from' => 'datetime:H:i',
            'time_to' => 'datetime:H:i',
            'reports' => 'array',
        ];
    }

    public function types(): array
    {
        return [
            'technical_discussions' => 'Technical Discussions',
            'kick_off' => 'Kick Off',
            'wrap_up' => 'Wrap Up',
            'other' => 'Other',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}
