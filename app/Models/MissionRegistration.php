<?php

namespace App\Models;

use App\Enums\MissionComponentUnit;
use App\Enums\MissionManagementTier;
use App\Enums\MissionOffice;
use App\Enums\MissionPsoCluster;
use App\Enums\MissionRpcoOffice;
use App\Enums\RegistrationSource;
use App\Enums\Sex;
use App\Enums\Suffix;
use Database\Factories\MissionRegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $mission_id
 * @property string $first_name
 * @property string|null $middle_initial
 * @property Suffix|null $suffix
 * @property string $last_name
 * @property MissionOffice $office
 * @property MissionManagementTier|null $management_tier
 * @property MissionPsoCluster|null $pso_cluster
 * @property MissionRpcoOffice|null $rpco_office
 * @property string $position
 * @property MissionComponentUnit|null $component_unit
 * @property string $contact_number
 * @property Sex $sex
 * @property string|null $email
 * @property array|null $attendance_dates
 * @property array|null $attendance_mode
 * @property RegistrationSource $source
 * @property int|null $registered_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['mission_id', 'first_name', 'middle_initial', 'suffix', 'last_name', 'office', 'management_tier', 'pso_cluster', 'rpco_office', 'position', 'component_unit', 'contact_number', 'sex', 'email', 'attendance_dates', 'attendance_mode', 'source', 'registered_by'])]
class MissionRegistration extends Model
{
    /** @use HasFactory<MissionRegistrationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'office' => MissionOffice::class,
            'management_tier' => MissionManagementTier::class,
            'pso_cluster' => MissionPsoCluster::class,
            'rpco_office' => MissionRpcoOffice::class,
            'component_unit' => MissionComponentUnit::class,
            'sex' => Sex::class,
            'suffix' => Suffix::class,
            'attendance_dates' => 'array',
            'attendance_mode' => 'array',
            'source' => RegistrationSource::class,
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function fullName(): string
    {
        $middleInitial = $this->middle_initial ? strtoupper($this->middle_initial).'.' : null;

        return collect([
            $this->first_name,
            $middleInitial,
            $this->last_name,
            $this->suffix?->label(),
        ])->filter()->implode(' ');
    }
}
