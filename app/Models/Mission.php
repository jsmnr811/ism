<?php

namespace App\Models;

use Database\Factories\MissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string|null $banner
 * @property string|null $agenda_link
 * @property string|null $virtual_site_visit_link
 * @property bool $enable_registration
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'start_date', 'end_date', 'banner', 'agenda_link', 'virtual_site_visit_link', 'enable_registration', 'is_active'])]
class Mission extends Model
{
    /** @use HasFactory<MissionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'enable_registration' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(MissionRegistration::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class);
    }
}
