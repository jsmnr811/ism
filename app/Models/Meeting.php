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
 * @property array|null $video_links
 * @property string|null $recording_link
 * @property string|null $transcription_link
 * @property string|null $report_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'description', 'date', 'time_from', 'time_to', 'type', 'other_type', 'image', 'venue', 'link', 'meeting_id', 'passcode', 'reports', 'video_links', 'recording_link', 'transcription_link', 'report_name', 'mission_id'])]
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
            'video_links' => 'array',
        ];
    }

    /**
     * Get the meeting reports as structured documents with a title, description, and url.
     *
     * @return list<array{title: string|null, description: string|null, url: string|null}>
     */
    public function getReportDocumentsAttribute(): array
    {
        $reports = $this->reports;

        if (! is_array($reports)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($report) {
            if (is_array($report)) {
                return [
                    'title' => $report['title'] ?? null,
                    'description' => $report['description'] ?? null,
                    'url' => $report['url'] ?? null,
                ];
            }

            if (! is_string($report) || $report === '') {
                return null;
            }

            return [
                'title' => null,
                'description' => null,
                'url' => $report,
            ];
        }, $reports)));
    }

    /**
     * Get the meeting video links that have both a title and a url.
     *
     * @return list<array{title: string, url: string}>
     */
    public function getVideoDocumentsAttribute(): array
    {
        $videos = $this->video_links;

        if (! is_array($videos)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($video) {
            if (! is_array($video)) {
                return null;
            }

            $title = trim((string) ($video['title'] ?? ''));
            $url = trim((string) ($video['url'] ?? ''));

            if ($title === '' || $url === '') {
                return null;
            }

            return [
                'title' => $title,
                'url' => $url,
            ];
        }, $videos)));
    }

    public function types(): array
    {
        return [
            'ribbon_cutting' => 'Ribbon Cutting',
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
