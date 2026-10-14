<?php

namespace App\Enums;

enum MissionOffice: string
{
    case WorldBank = 'wb';
    case EuropeanUnion = 'eu';
    case LocalGovernmentUnit = 'lgu';
    case DAManagement = 'da_management';
    case OversightAgency = 'oversight_agency';
    case NPAB = 'npab';
    case RPABContractor = 'rpab_contractor';
    case PropenetGroup = 'propenet_group';
    case PRDP = 'prdp';
    case Guest = 'guest';

    public static function labels(): array
    {
        return [
            self::WorldBank->value => 'World Bank (WB)',
            self::EuropeanUnion->value => 'European Union (EU)',
            self::LocalGovernmentUnit->value => 'Local Government Unit (LGU)',
            self::DAManagement->value => 'DA Management (not involved in PRDP)',
            self::OversightAgency->value => 'Oversight Agency',
            self::NPAB->value => 'NPAB',
            self::RPABContractor->value => 'RPAB Contractor',
            self::PropenetGroup->value => 'Propenet Group',
            self::PRDP->value => 'PRDP',
            self::Guest->value => 'Guest',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public static function isPRDP(self $office): bool
    {
        return $office === self::PRDP;
    }
}
