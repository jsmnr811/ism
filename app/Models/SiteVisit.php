<?php

namespace App\Models;

use App\Enums\SiteVisitCluster;
use App\Enums\SiteVisitComponent;
use App\Enums\SiteVisitFundingSource;
use Database\Factories\SiteVisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $sp_name
 * @property SiteVisitCluster|null $cluster
 * @property SiteVisitComponent|null $component
 * @property string|null $location
 * @property SiteVisitFundingSource|null $funding_source
 * @property string|null $document_link
 * @property string|null $virtual_site_visit_link
 * @property Carbon|null $date
 * @property int|null $mission_id
 * @property array|null $images
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['sp_name', 'cluster', 'component', 'location', 'funding_source', 'document_link', 'virtual_site_visit_link', 'date', 'images', 'mission_id'])]
class SiteVisit extends Model
{
    /** @use HasFactory<SiteVisitFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'cluster' => SiteVisitCluster::class,
            'component' => SiteVisitComponent::class,
            'funding_source' => SiteVisitFundingSource::class,
            'date' => 'datetime:Y-m-d',
            'images' => 'array',
        ];
    }
}
